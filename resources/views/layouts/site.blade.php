{{-- Rendered by App\View\Components\SiteLayout (<x-site-layout>), which prepares the menus and SEO tags. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['seoTags' => $seoTags])
        @include('partials.site-theme')
    </head>
    <body class="flex min-h-screen flex-col antialiased" data-site-theme>
        @include('partials.impersonation-banner')

        {{-- Navigation comes from the menu builder (the "main" menu), see MenuServiceProvider. --}}
        <flux:header container class="sticky top-0 flex border-b *:h-auto border-zinc-200 backdrop-blur dark:border-zinc-700" data-site-menubar>
            <x-app-logo href="{{ route('home') }}" />

            <flux:spacer />

            @if ($mainMenu->count() > 0)
                <x-menu.navbar :menu="$mainMenu" class="-mb-px max-sm:hidden" data-test="main-menu" />

                {{--
                    Mobile menu: a sidebar that slides in from the right. Not a Flux flyout: its scroll lock
                    reserves the scrollbar gutter, which keeps the panel off the right edge.
                --}}
                <div x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="sm:hidden">
                    <flux:button variant="ghost" icon="bars-3" x-on:click="open = true" x-bind:aria-expanded="open" aria-controls="site-menu-sidebar" :aria-label="__('Open menu')" data-test="site-menu-button" />

                    <template x-teleport="body">
                        <div x-show="open" x-cloak class="fixed inset-0 z-50 sm:hidden" role="dialog" aria-modal="true" aria-label="{{ __('Menu') }}">
                            <div
                                x-show="open"
                                x-transition:enter="transition-opacity duration-200 ease-out"
                                x-transition:enter-start="opacity-0"
                                x-transition:leave="transition-opacity duration-150 ease-in"
                                x-transition:leave-end="opacity-0"
                                x-on:click="open = false"
                                class="absolute inset-0 bg-black/50"
                            ></div>

                            <nav
                                id="site-menu-sidebar"
                                x-show="open"
                                x-trap.noscroll.inert="open"
                                x-transition:enter="transition-transform duration-200 ease-out"
                                x-transition:enter-start="translate-x-full"
                                x-transition:enter-end="translate-x-0"
                                x-transition:leave="transition-transform duration-150 ease-in"
                                x-transition:leave-start="translate-x-0"
                                x-transition:leave-end="translate-x-full"
                                class="absolute inset-y-0 right-0 flex w-80 max-w-[85vw] flex-col gap-6 overflow-y-auto border-s border-zinc-200 p-6 shadow-xl dark:border-zinc-700"
                                data-site-menu-panel
                                data-test="site-menu-sidebar"
                            >
                                <div class="flex items-center justify-between gap-4">
                                    <x-app-logo href="{{ route('home') }}" />
                                    <flux:button variant="ghost" size="sm" icon="x-mark" x-on:click="open = false" :aria-label="__('Close menu')" />
                                </div>

                                <flux:navlist :aria-label="__('Menu')">
                                    @foreach ($mainMenu as $link)
                                        <flux:navlist.item :href="$link->url()" :icon="$link->iconName()" :current="$link->isCurrent()">{{ $link->text() }}</flux:navlist.item>
                                    @endforeach
                                </flux:navlist>
                            </nav>
                        </div>
                    </template>
                </div>
            @endif
        </flux:header>


        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="space-y-4 border-t border-zinc-200 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            @if ($footerMenu->count() > 0)
                <nav class="flex flex-wrap justify-center gap-x-6 gap-y-2" data-test="footer-menu">
                    @foreach ($footerMenu as $link)
                        <a href="{{ $link->url() }}" class="hover:text-zinc-800 hover:underline dark:hover:text-white">{{ $link->text() }}</a>
                    @endforeach
                </nav>
            @endif

            <p>&copy; {{ now()->year }} {{ $siteTitle }}</p>

            {{-- Editors jump from the page or blog they are looking at straight into its brick editor. --}}
            @if ($seo instanceof App\Bricks\Brickable)
                @can('update', $seo)
                    <p>
                        <a href="{{ route('admin.'.$seo->adminSection().'.edit', $seo) }}" class="inline-flex items-center gap-1.5 hover:text-zinc-800 hover:underline dark:hover:text-white" data-test="edit-content-link">
                            <flux:icon name="pencil-square" variant="micro" />
                            {{ $seo instanceof App\Models\Blog ? __('Edit blog') : __('Edit page') }}
                        </a>
                    </p>
                @endcan
            @endif
        </footer>

        @fluxScripts
    </body>
</html>
