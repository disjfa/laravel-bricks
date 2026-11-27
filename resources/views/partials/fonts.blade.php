{{--
    The fonts chosen on the admin settings page (App\Settings\FontSettings), loaded from Bunny Fonts. Without a
    choice the system font is used and nothing is downloaded.

    Elements with data-site-fonts (the admin body) or data-site-theme (the website and the previews) get the
    text font, and their headings the heading font.
--}}
@inject('fonts', App\Settings\FontSettings::class)

@if ($fontStylesheet = $fonts->stylesheetUrl())
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="{{ $fontStylesheet }}">
@endif

<style>
    {{-- Unescaped: the font stacks contain quotes, and they only come from App\Enums\SiteFont, never from input. --}}
    :is([data-site-fonts], [data-site-theme]) {
        --site-font-text: {!! $fonts->textFamily() !!};
        --site-font-heading: {!! $fonts->headingFamily() !!};

        font-family: var(--site-font-text);
    }

    :is([data-site-fonts], [data-site-theme]) :is(h1, h2, h3, h4, h5, h6, [data-flux-heading]) {
        font-family: var(--site-font-heading);
    }
</style>
