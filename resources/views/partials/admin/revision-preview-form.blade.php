{{--
    The preview of a form revision on the Revisions tab: its settings and its fields, rendered with the website views.
    Used together with App\Livewire\Concerns\ManagesRevisions.
--}}
<x-admin.screen.column divided class="space-y-4" data-test="revision-preview">
    @if ($revision = $this->selectedRevision)
        @php($snapshot = $revision->snapshot)

        <flux:card class="space-y-3 p-4!" data-test="revision-summary">
            <flux:heading>{{ $snapshot['name'] ?? '' }}</flux:heading>

            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Submit button') }}</dt>
                <dd>{{ filled($snapshot['submit_label'] ?? null) ? $snapshot['submit_label'] : __('Send') }}</dd>

                @if (filled($snapshot['success_message'] ?? null))
                    <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Success message') }}</dt>
                    <dd>{{ $snapshot['success_message'] }}</dd>
                @endif
            </dl>
        </flux:card>

        <livewire:form-preview
            :fields="$this->previewChildren('fields')"
            :submit-label="filled($snapshot['submit_label'] ?? null) ? $snapshot['submit_label'] : __('Send')"
            :description="$snapshot['description'] ?? ''"
            wire:key="revision-preview-{{ $revision->id }}"
        />
    @endif
</x-admin.screen.column>
