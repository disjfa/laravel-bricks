{{--
    Lets visitors install the website as an app, see App\Http\Controllers\ManifestController.
    The theme-color follows the menu bar color of the light and dark theme.
--}}
@php($palette = App\Enums\ThemeColor::palette())

<link rel="manifest" href="{{ route('manifest') }}">
<meta name="theme-color" media="(prefers-color-scheme: light)" content="{{ $palette['light']['menubar_background'] }}">
<meta name="theme-color" media="(prefers-color-scheme: dark)" content="{{ $palette['dark']['menubar_background'] }}">

{{-- Safari on iOS reads these instead of the manifest when the site is added to the home screen. --}}
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ App\Models\Setting::appShortName() }}">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="application-name" content="{{ App\Models\Setting::appShortName() }}">
