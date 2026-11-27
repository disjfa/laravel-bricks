@props(['icon' => null])

{{-- The title of an admin page, with the icon of its item in the admin menu. --}}
<div {{ $attributes->class('flex min-w-0 items-center gap-2') }}>
    @if ($icon)
        <flux:icon :name="$icon" class="size-6 shrink-0 text-zinc-500 dark:text-white/70" />
    @endif

    <flux:heading size="xl" level="1" class="truncate">{{ $slot }}</flux:heading>
</div>
