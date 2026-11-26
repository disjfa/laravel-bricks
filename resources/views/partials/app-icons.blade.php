{{-- Favicons and Apple touch icons: the uploaded app icon (see the admin settings) or the defaults. --}}
@if (App\Models\Setting::appIconUrl('icon-192.png'))
    @foreach (App\Actions\Pwa\GenerateAppIcons::FaviconSizes as $size)
        <link rel="icon" type="image/png" sizes="{{ $size }}x{{ $size }}" href="{{ App\Models\Setting::appIconUrl("favicon-{$size}.png") }}">
    @endforeach
    <link rel="icon" type="image/png" sizes="192x192" href="{{ App\Models\Setting::appIconUrl('icon-192.png') }}">

    @foreach (App\Actions\Pwa\GenerateAppIcons::AppleSizes as $size)
        <link rel="apple-touch-icon" sizes="{{ $size }}x{{ $size }}" href="{{ App\Models\Setting::appIconUrl("apple-touch-icon-{$size}.png") }}">
    @endforeach
@else
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif
