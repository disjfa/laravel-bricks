<div>
    <flux:button :variant="($data['variant'] ?? 'primary') === 'outline' ? 'outline' : 'primary'" :href="$data['url'] ?? '#'">
        {{ $data['label'] ?? '' }}
    </flux:button>
</div>
