@props(['column', 'direction' => null])

{{-- A table column that sorts the list, for components with App\Livewire\Concerns\QueriesWithQueryBuilder. --}}
<flux:table.column sortable :sorted="$direction !== null" :direction="$direction" wire:click="sortBy('{{ $column }}')" {{ $attributes }}>
    {{ $slot }}
</flux:table.column>
