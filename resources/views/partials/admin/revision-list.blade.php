{{--
    The list of revisions on the Revisions tab, used together with App\Livewire\Concerns\ManagesRevisions.
    Newest first: the top one is the version that is saved now. Clicking one shows it in the preview next to it.
--}}
<x-admin.screen.column class="space-y-4" data-test="revision-list">
    <flux:text>{{ __('A version is kept every time you save. Pick one to see it, and revert to go back to it. Reverting is a new version too, so nothing is lost.') }}</flux:text>

    @forelse ($this->revisions as $revision)
        @php($isSelected = $revision->id === $this->selectedRevision?->id)
        @php($isCurrent = $revision->id === $this->currentRevisionId)

        <div
            wire:key="revision-{{ $revision->id }}"
            @class([
                'rounded-lg border p-4 transition',
                'border-accent ring-1 ring-accent' => $isSelected,
                'border-zinc-200 hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500' => ! $isSelected,
            ])
            data-test="revision"
        >
            <div class="flex flex-wrap items-start justify-between gap-3">
                <button type="button" wire:click="selectRevision({{ $revision->id }})" class="min-w-0 flex-1 space-y-1 text-start" @if ($isSelected) aria-current="true" @endif>
                    <span class="flex flex-wrap items-center gap-2">
                        <flux:badge size="sm" :color="$revision->action->color()">{{ $revision->action->label() }}</flux:badge>

                        @if ($isCurrent)
                            <flux:badge size="sm" color="green" icon="check">{{ __('Current version') }}</flux:badge>
                        @endif
                    </span>

                    <span class="block font-medium text-zinc-800 dark:text-white" title="{{ $revision->created_at?->isoFormat('LLLL') }}">
                        {{ $revision->created_at?->isoFormat('LLL') }}
                        <span class="font-normal text-zinc-500 dark:text-zinc-400">· {{ $revision->created_at?->diffForHumans() }}</span>
                    </span>

                    <span class="block text-sm text-zinc-500 dark:text-zinc-400">
                        {{ $revision->user?->name ?? __('System') }}

                        @if ($revision->revertedFrom)
                            · {{ __('back to the version of :date', ['date' => $revision->revertedFrom->created_at?->isoFormat('LLL')]) }}
                        @endif
                    </span>
                </button>

                @unless ($isCurrent)
                    <flux:button
                        size="sm"
                        icon="arrow-uturn-left"
                        wire:click="revert({{ $revision->id }})"
                        wire:confirm="{{ __('Go back to the version of :date? The current version stays in the list.', ['date' => $revision->created_at?->isoFormat('LLL')]) }}"
                        data-test="revert-button"
                    >
                        {{ __('Revert') }}
                    </flux:button>
                @endunless
            </div>
        </div>
    @empty
        <div class="rounded-xl border-2 border-dashed border-zinc-200 px-6 py-16 text-center dark:border-zinc-700">
            <flux:icon name="clock" class="mx-auto size-10 text-zinc-400" />
            <flux:heading class="mt-4">{{ __('No versions yet') }}</flux:heading>
            <flux:text class="mt-1">{{ __('The first version is kept the next time you save.') }}</flux:text>
        </div>
    @endforelse

    <flux:pagination :paginator="$this->revisions" />
</x-admin.screen.column>
