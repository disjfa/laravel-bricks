<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@if (isset($seoTags))
    {{-- The website: title, description and social tags, prepared by App\View\Components\SiteLayout. --}}
    {{ $seoTags }}
@else
    <title>
        {{ filled($title ?? null) ? $title.' - '.app(App\Settings\SeoSettings::class)->title() : app(App\Settings\SeoSettings::class)->title() }}
    </title>
@endif

@include('partials.app-icons')

@if (isset($seoTags))
    @include('partials.pwa')
@endif

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
