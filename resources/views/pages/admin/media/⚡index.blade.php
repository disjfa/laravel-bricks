<?php

use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

new #[Layout('layouts::admin'), Title('Media')] class extends Component {
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    /**
     * One of: all, images, documents.
     */
    #[Url(except: 'all')]
    public string $type = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function deleteMedia(int $mediaId): void
    {
        $media = Media::query()->findOrFail($mediaId);

        $this->authorize('delete', $media);

        $media->delete();

        Flux::toast(variant: 'success', text: __('File deleted.'));
    }

    /**
     * @return LengthAwarePaginator<int, Media>
     */
    #[Computed]
    public function mediaItems(): LengthAwarePaginator
    {
        $this->authorize('viewAny', Media::class);

        return Media::query()
            ->with('model')
            ->when($this->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%");
                });
            })
            ->when($this->type === 'images', fn ($query) => $query->where('mime_type', 'like', 'image/%'))
            ->when($this->type === 'documents', fn ($query) => $query->where('mime_type', 'not like', 'image/%'))
            ->latest('id')
            ->paginate(24);
    }

    #[Computed]
    public function totalSize(): string
    {
        $bytes = (int) Media::query()->sum('size');

        return Illuminate\Support\Number::fileSize($bytes, precision: 1);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Media') }}</flux:heading>
            <flux:text class="mt-1">
                {{ trans_choice('{0} No files yet.|{1} 1 file, :size in total.|[2,*] :count files, :size in total.', $this->mediaItems->total(), ['size' => $this->totalSize]) }}
            </flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <flux:select wire:model.live="type" class="max-w-40">
                <flux:select.option value="all">{{ __('All files') }}</flux:select.option>
                <flux:select.option value="images">{{ __('Images') }}</flux:select.option>
                <flux:select.option value="documents">{{ __('Documents') }}</flux:select.option>
            </flux:select>

            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search files...')" class="max-w-xs" clearable />
        </div>
    </div>

    <flux:callout icon="information-circle" variant="secondary">
        <flux:callout.text>{{ __('Files are uploaded per page. Open a page and go to its Media tab to add files.') }}</flux:callout.text>
    </flux:callout>

    @if ($this->mediaItems->isEmpty())
        <flux:text class="py-12 text-center">{{ __('No files found.') }}</flux:text>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($this->mediaItems as $media)
                <x-admin.media-card :media="$media" :show-owner="true" wire:key="media-{{ $media->id }}" />
            @endforeach
        </div>

        <flux:pagination :paginator="$this->mediaItems" />
    @endif
</div>
