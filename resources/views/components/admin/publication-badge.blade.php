@props(['model'])

{{-- The publication state of a model that uses App\Concerns\HasPublishing. --}}
<flux:badge size="sm" :color="$model->publicationColor()" {{ $attributes }}>{{ $model->publicationLabel() }}</flux:badge>
