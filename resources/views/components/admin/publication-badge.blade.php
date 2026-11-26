@props(['model'])

{{-- The publication state of a model that uses App\Concerns\PublishedTrait. --}}
<flux:badge size="sm" :color="$model->publicationColor()" {{ $attributes }}>{{ $model->publicationLabel() }}</flux:badge>
