<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@if ($seo ?? false)
    {{-- The website: title, description and social tags from romanzipp/laravel-seo, see layouts/site. --}}
    {{ seo()->render() }}
@else
    <title>
        {{ filled($title ?? null) ? $title.' - '.App\Models\Setting::metaTitle() : App\Models\Setting::metaTitle() }}
    </title>
@endif

@include('partials.app-icons')

@if ($seo ?? false)
    @include('partials.pwa')
@endif

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
