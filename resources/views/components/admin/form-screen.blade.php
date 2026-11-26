@props(['form', 'current', 'save' => null, 'columns' => false])

{{-- <x-admin.screen> for a form of the form builder, with its Fields, Settings and Submissions tabs. --}}
@php
    $tabs = [
        ['label' => __('Fields'), 'icon' => 'queue-list', 'url' => route('admin.forms.edit', $form), 'current' => $current === 'fields'],
        ['label' => __('Settings'), 'icon' => 'cog-6-tooth', 'url' => route('admin.forms.settings', $form), 'current' => $current === 'settings'],
        ['label' => __('Submissions'), 'icon' => 'inbox', 'url' => route('admin.forms.submissions', $form), 'current' => $current === 'submissions'],
    ];
@endphp

<x-admin.screen
    :title="$form->name"
    :back="route('admin.forms.index')"
    :tabs="$tabs"
    :save="$save"
    :columns="$columns"
    {{ $attributes }}
>
    {{ $slot }}
</x-admin.screen>
