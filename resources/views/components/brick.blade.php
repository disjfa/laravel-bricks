@props(['type', 'data', 'settings' => []])

{{-- Renders a brick on the website inside the container for its width setting. --}}
@php($width = App\Enums\BrickWidth::fromSettings($settings))

<div {{ $attributes->class($width->containerClass()) }} data-brick-width="{{ $width->value }}">
    @include($type->view(), ['data' => $data, 'settings' => $settings, 'width' => $width])
</div>
