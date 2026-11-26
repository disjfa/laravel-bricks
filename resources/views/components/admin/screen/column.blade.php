@props(['divided' => false])

{{-- One column of <x-admin.screen columns>, scrolling on its own from lg up. --}}
<div {{ $attributes->class([
    'p-4 lg:min-h-0 lg:overflow-y-auto lg:overscroll-contain',
    'lg:border-s lg:border-zinc-200 lg:dark:border-zinc-700' => $divided,
]) }}>
    {{ $slot }}
</div>
