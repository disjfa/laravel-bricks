@props(['title' => null, 'description' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])

        @if (filled($description))
            <meta name="description" content="{{ $description }}">
        @endif
    </head>
    <body class="flex min-h-screen flex-col bg-white text-zinc-800 antialiased dark:bg-zinc-900 dark:text-zinc-100">
        @include('partials.impersonation-banner')

        <flux:header container class="border-b border-zinc-200 bg-white/80 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/80">
            <x-app-logo href="{{ route('home') }}" />

            <flux:spacer />

            <x-menu.navbar :menu="Spatie\Menu\Laravel\Menu::main()" class="-mb-px max-sm:hidden" />

            <div class="ms-4 flex items-center gap-2">
                @auth
                    <x-desktop-user-menu :showTeam="false" />
                @else
                    <flux:button variant="ghost" size="sm" :href="route('login')">{{ __('Log in') }}</flux:button>

                    @if (Route::has('register'))
                        <flux:button variant="primary" size="sm" :href="route('register')">{{ __('Register') }}</flux:button>
                    @endif
                @endauth
            </div>
        </flux:header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 py-8 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            &copy; {{ now()->year }} {{ config('app.name') }}
        </footer>

        @fluxScripts
    </body>
</html>
