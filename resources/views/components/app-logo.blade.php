@props([
    'sidebar' => false,
])

{{-- The site title and logo from the admin settings. An uploaded logo is shown without the default badge. --}}
@php
    $siteTitle = App\Models\Setting::siteTitle();
    $hasLogo = App\Models\Setting::siteLogoUrl() !== null;
    $markClass = $hasLogo
        ? 'flex h-8 max-w-32 items-center'
        : 'flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground';
    $iconClass = $hasLogo ? 'max-h-8' : 'size-5 fill-current text-white dark:text-black';
@endphp

@if ($sidebar)
    <flux:sidebar.brand :name="$siteTitle" {{ $attributes }}>
        <x-slot name="logo" :class="$markClass">
            <x-app-logo-icon :class="$iconClass" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$siteTitle" {{ $attributes }}>
        <x-slot name="logo" :class="$markClass">
            <x-app-logo-icon :class="$iconClass" />
        </x-slot>
    </flux:brand>
@endif
