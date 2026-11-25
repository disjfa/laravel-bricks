<?php

use App\Models\Page;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

new #[Layout('layouts::admin')] class extends Component {
    use WithFileUploads;

    public Page $page;

    /**
     * Files land here while uploading and are attached to the page as soon as they arrive.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $uploads = [];

    public function mount(Page $page): void
    {
        $this->authorize('update', $page);

        $this->page = $page;
    }

    public function updatedUploads(): void
    {
        $this->authorize('update', $this->page);

        try {
            $this->validate([
                'uploads' => ['array', 'max:20'],
                'uploads.*' => [
                    'file',
                    'max:10240',
                    // No svg or html: those can run scripts when opened from our own domain.
                    'mimes:jpg,jpeg,png,gif,webp,avif,pdf,txt,csv,doc,docx,xls,xlsx,ppt,pptx,zip,mp3,mp4',
                ],
            ], [
                'uploads.*.mimes' => __('This file type is not allowed.'),
                'uploads.*.max' => __('Files may not be larger than 10 MB.'),
            ]);
        } catch (ValidationException $exception) {
            $this->reset('uploads');

            throw $exception;
        }

        foreach ($this->uploads as $upload) {
            $this->page
                ->addMedia($upload)
                ->usingName(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection(Page::MediaCollection);
        }

        Flux::toast(variant: 'success', text: trans_choice('{1} File uploaded.|[2,*] :count files uploaded.', count($this->uploads)));

        $this->reset('uploads');
        unset($this->mediaItems);
    }

    public function deleteMedia(int $mediaId): void
    {
        $this->authorize('update', $this->page);

        $this->page->media()->findOrFail($mediaId)->delete();

        unset($this->mediaItems);

        Flux::toast(variant: 'success', text: __('File deleted.'));
    }

    /**
     * @return Collection<int, Media>
     */
    #[Computed]
    public function mediaItems(): Collection
    {
        return $this->page->media()
            ->where('collection_name', Page::MediaCollection)
            ->latest('id')
            ->get();
    }

    public function render()
    {
        return $this->view()->title(__(':title media', ['title' => $this->page->title]));
    }
}; ?>

<div>
    <x-admin.page-header :page="$page" current="media" />

    <div class="space-y-6">
        {{-- Dropzone: dropped or picked files upload immediately (updatedUploads attaches them to the page) --}}
        <div
            x-data="{ dragging: false }"
            x-on:dragover.prevent="dragging = true"
            x-on:dragleave.prevent="dragging = false"
            x-on:drop.prevent="dragging = false; $event.dataTransfer.files.length && $wire.uploadMultiple('uploads', [...$event.dataTransfer.files])"
            x-bind:class="dragging ? 'border-zinc-500 bg-zinc-50 dark:border-zinc-400 dark:bg-zinc-800' : 'border-zinc-300 dark:border-zinc-600'"
            class="rounded-xl border-2 border-dashed px-6 py-10 text-center transition"
            data-test="media-dropzone"
        >
            <input
                x-ref="fileInput"
                type="file"
                wire:model="uploads"
                multiple
                accept=".jpg,.jpeg,.png,.gif,.webp,.avif,.pdf,.txt,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.mp3,.mp4"
                class="sr-only"
                tabindex="-1"
                data-test="media-file-input"
            >

            <div wire:loading.remove wire:target="uploads">
                <flux:icon name="cloud-arrow-up" class="mx-auto size-10 text-zinc-400" />
                <flux:heading class="mt-3">{{ __('Drop files here to upload') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Images, PDFs and documents up to 10 MB. They are attached to this page right away.') }}</flux:text>

                <flux:button class="mt-4" icon="plus" x-on:click="$refs.fileInput.click()">{{ __('Choose files') }}</flux:button>
            </div>

            <div wire:loading.flex wire:target="uploads" class="flex-col items-center gap-3">
                <flux:icon.loading class="size-8 text-zinc-400" />
                <flux:text>{{ __('Uploading...') }}</flux:text>
            </div>
        </div>

        @if ($errors->has('uploads.*') || $errors->has('uploads'))
            <flux:callout variant="danger" icon="exclamation-triangle" :heading="$errors->first('uploads.*') ?: $errors->first('uploads')" />
        @endif

        {{-- Files attached to this page --}}
        @if ($this->mediaItems->isEmpty())
            <flux:text class="py-6 text-center">{{ __('No files yet.') }}</flux:text>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($this->mediaItems as $media)
                    <x-admin.media-card :media="$media" wire:key="media-{{ $media->id }}" />
                @endforeach
            </div>
        @endif
    </div>
</div>
