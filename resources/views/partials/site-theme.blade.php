{{--
    The theme colors from the admin settings page as CSS custom properties. This style block is not in a
    cascade layer, so it wins over the Tailwind theme and the Flux defaults. Only elements inside
    [data-site-theme] get the colors (the website body and the brick preview), the admin keeps its own.
--}}
@php($palette = App\Enums\ThemeColor::palette())

<style>
    [data-site-theme] {
        @foreach (App\Enums\ThemeColor::cases() as $color)
            @foreach ($color->cssVariables() as $variable)
                {{ $variable }}: {{ $palette['light'][$color->value] }};
            @endforeach
        @endforeach
    }

    .dark [data-site-theme],
    .dark[data-site-theme] {
        @foreach (App\Enums\ThemeColor::cases() as $color)
            @foreach ($color->cssVariables() as $variable)
                {{ $variable }}: {{ $palette['dark'][$color->value] }};
            @endforeach
        @endforeach
    }

    [data-site-theme] {
        background-color: var(--site-background);
        color: var(--site-text);
    }

    [data-site-menubar] {
        background-color: color-mix(in srgb, var(--site-menubar-background) 85%, transparent);
        color: var(--site-menubar-text);
    }

    [data-site-menu-panel] {
        background-color: var(--site-menubar-background);
        color: var(--site-menubar-text);
    }

    [data-site-menu-panel] [data-flux-brand] div,
    [data-site-menu-panel] [data-flux-navlist-item]:not([data-current]),
    [data-site-menubar] [data-flux-brand] div,
    [data-site-menubar] [data-flux-navbar-items]:not([data-current]) {
        color: var(--site-menubar-text);
    }
</style>
