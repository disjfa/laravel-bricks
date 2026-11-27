<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        {{-- The admin uses the colors and fonts of the website, see the settings page. --}}
        @include('partials.site-theme')
    </head>
    <body class="min-h-screen" data-site-theme>
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 dark:border-zinc-700" data-site-menubar>
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('admin.dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                @foreach (Spatie\Menu\Laravel\Menu::adminGroups() as $heading => $menu)
                    @if (count($menu) > 0)
                        <flux:sidebar.group :heading="$heading" class="grid">
                            <x-menu.sidebar :menu="$menu" />
                        </flux:sidebar.group>
                    @endif
                @endforeach
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="globe-alt" :href="route('home')">
                    {{ __('View website') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" />
        </flux:sidebar>

        <flux:header class="border-b border-zinc-200 lg:hidden dark:border-zinc-700" data-site-menubar>
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <x-desktop-user-menu />
        </flux:header>

        {{-- 16px padding at every size. Screens that mark themselves data-full-bleed (<x-admin.screen>) manage their own spacing. --}}
        <flux:main class="p-4! has-[[data-full-bleed]]:p-0!">
            {{ $slot }}
        </flux:main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
