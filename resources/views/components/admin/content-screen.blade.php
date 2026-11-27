@props(['model', 'current', 'save' => null, 'columns' => false, 'actions' => null])

{{-- <x-admin.screen> for a page or blog (an App\Bricks\Brickable), with its Content, Settings, Media and Revisions tabs. --}}
@php
    $section = $model->adminSection();
    $icon = match ($section) {
        'blogs' => 'newspaper',
        default => 'document-text',
    };
    $tabs = [
        ['label' => __('Content'), 'icon' => 'squares-2x2', 'url' => route('admin.'.$section.'.edit', $model), 'current' => $current === 'content'],
        ['label' => __('Settings'), 'icon' => 'cog-6-tooth', 'url' => route('admin.'.$section.'.settings', $model), 'current' => $current === 'settings'],
        ['label' => __('Media'), 'icon' => 'photo', 'url' => route('admin.'.$section.'.media', $model), 'current' => $current === 'media'],
        ['label' => __('Revisions'), 'icon' => 'clock', 'url' => route('admin.'.$section.'.revisions', $model), 'current' => $current === 'revisions'],
    ];
@endphp

<x-admin.screen
    :title="$model->title"
    :icon="$icon"
    :back="route('admin.'.$section.'.index')"
    :subtitle="$model->publicPath()"
    :subtitle-url="$model->url()"
    :tabs="$tabs"
    :view-url="$model->url()"
    :save="$save"
    :columns="$columns"
    {{ $attributes }}
>
    <x-slot:badge>
        <x-admin.publication-badge :model="$model" />
    </x-slot:badge>

    @if ($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endif

    {{ $slot }}
</x-admin.screen>
