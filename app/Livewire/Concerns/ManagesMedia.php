<?php

namespace App\Livewire\Concerns;

use App\Bricks\Brickable;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The media panel of a page or blog. Render it with the partials.admin.media-panel view.
 * The component also needs Livewire\WithFileUploads.
 */
trait ManagesMedia
{
    /**
     * Files land here while uploading and are attached as soon as they arrive.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $uploads = [];

    abstract protected function brickable(): Brickable;

    public function updatedUploads(): void
    {
        $brickable = $this->brickable();

        $this->authorize('update', $brickable);

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
            $brickable
                ->addMedia($upload)
                ->usingName(pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection(Brickable::MediaCollection);
        }

        Flux::toast(variant: 'success', text: trans_choice('{1} File uploaded.|[2,*] :count files uploaded.', count($this->uploads)));

        $this->reset('uploads');
        unset($this->mediaItems);
    }

    public function deleteMedia(int $mediaId): void
    {
        $brickable = $this->brickable();

        $this->authorize('update', $brickable);

        $brickable->media()->findOrFail($mediaId)->delete();

        unset($this->mediaItems);

        Flux::toast(variant: 'success', text: __('File deleted.'));
    }

    /**
     * @return Collection<int, Media>
     */
    #[Computed]
    public function mediaItems(): Collection
    {
        $brickable = $this->brickable();

        return Media::query()
            ->where('model_type', $brickable->getMorphClass())
            ->where('model_id', $brickable->getKey())
            ->where('collection_name', Brickable::MediaCollection)
            ->latest('id')
            ->get();
    }
}
