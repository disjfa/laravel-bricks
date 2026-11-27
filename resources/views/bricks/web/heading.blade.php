@php
    $level = in_array((int) ($data['level'] ?? 2), [2, 3, 4], true) ? (int) $data['level'] : 2;
    $classes = match ($level) {
        2 => 'text-3xl font-bold tracking-tight',
        3 => 'text-2xl font-semibold',
        4 => 'text-xl font-semibold',
    };
@endphp

<h{{ $level }} class="{{ $classes }} text-balance {{ App\Enums\TextAlign::fromSettings($settings)->textClass() }}">{{ $data['text'] ?? '' }}</h{{ $level }}>
