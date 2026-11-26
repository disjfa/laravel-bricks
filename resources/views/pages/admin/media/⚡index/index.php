<?php

use App\Livewire\Concerns\QueriesWithQueryBuilder;
use App\Queries\MediaQuery;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

new #[Layout('layouts::admin'), Title('Media')] class extends Component
{
    use QueriesWithQueryBuilder;
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

        return MediaQuery::for(Media::query()->with('model'), $this->queryRequest(MediaQuery::class, [
            'search' => $this->search,
            'type' => $this->type === 'all' ? null : $this->type,
        ]))->paginate(24);
    }

    #[Computed]
    public function totalSize(): string
    {
        $bytes = (int) Media::query()->sum('size');

        return Number::fileSize($bytes, precision: 1);
    }
};
