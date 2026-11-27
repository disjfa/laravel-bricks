@props([
    'model',
    'label' => null,
    'description' => null,
    'options' => [],
    'selected' => [],
    'placeholder' => null,
])

{{--
    A Tom Select for tags or categories (tagSelect in resources/js/tom-select.js): pick from the options or type a
    new name. The chosen names are written to the Livewire property in "model". It lives in wire:ignore, so after
    loading the component keeps it up to date in the browser.
--}}
<flux:field {{ $attributes }}>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div wire:ignore x-data="tagSelect({{ Js::from($model) }})">
        <select
            x-ref="select"
            multiple
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            class="block min-h-10 w-full rounded-lg border border-zinc-200 border-b-zinc-300/80 bg-white px-2 py-1 text-base shadow-xs sm:text-sm dark:border-white/10 dark:bg-white/10"
        >
            @foreach (collect($options)->merge($selected)->unique() as $option)
                <option value="{{ $option }}" @selected(in_array($option, $selected, true))>{{ $option }}</option>
            @endforeach
        </select>
    </div>

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    <flux:error :name="$model" />
    <flux:error :name="$model.'.*'" />
</flux:field>
