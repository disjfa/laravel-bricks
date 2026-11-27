{{--
    The preview of a page or blog revision on the Revisions tab: its settings and its bricks, rendered with the website views.
    Used together with App\Livewire\Concerns\ManagesRevisions.
--}}
<x-admin.screen.column divided class="space-y-4" data-test="revision-preview">
    @if ($revision = $this->selectedRevision)
        @php($snapshot = $revision->snapshot)

        <flux:card class="space-y-3 p-4!" data-test="revision-summary">
            <flux:heading>{{ $snapshot['title'] ?? '' }}</flux:heading>

            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Slug') }}</dt>
                <dd class="font-mono">{{ $snapshot['slug'] ?? '' }}</dd>

                <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</dt>
                <dd>{{ App\Enums\PublishStatus::tryFrom($snapshot['status'] ?? '')?->label() }}</dd>

                @if (filled($snapshot['excerpt'] ?? null))
                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Excerpt') }}</dt>
                    <dd>{{ $snapshot['excerpt'] }}</dd>
                @endif

                @if (filled($snapshot['categories'] ?? null))
                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Categories') }}</dt>
                    <dd>{{ implode(', ', $snapshot['categories']) }}</dd>
                @endif

                @if (filled($snapshot['tags'] ?? null))
                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Tags') }}</dt>
                    <dd>{{ implode(', ', $snapshot['tags']) }}</dd>
                @endif

                @if (filled($snapshot['meta_title'] ?? null))
                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Meta title') }}</dt>
                    <dd>{{ $snapshot['meta_title'] }}</dd>
                @endif
            </dl>
        </flux:card>

        <livewire:brick-preview :bricks="$this->previewChildren('bricks')" wire:key="revision-preview-{{ $revision->id }}" />
    @endif
</x-admin.screen.column>
