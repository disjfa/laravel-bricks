<?php

use App\Models\Form;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::admin')] class extends Component {
    use WithPagination;

    public Form $form;

    public function mount(Form $form): void
    {
        $this->authorize('update', $form);

        $this->form = $form;
    }

    public function deleteSubmission(int $submissionId): void
    {
        $this->authorize('update', $this->form);

        $this->form->submissions()->findOrFail($submissionId)->delete();

        Flux::toast(variant: 'success', text: __('Submission deleted.'));
    }

    /**
     * @return LengthAwarePaginator<int, App\Models\FormSubmission>
     */
    #[Computed]
    public function submissions(): LengthAwarePaginator
    {
        return $this->form->submissions()->latest('id')->paginate(20);
    }

    /**
     * The labels of the current fields, keyed by name. Answers to removed fields are shown with their key.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function labels(): array
    {
        return $this->form->fields->mapWithKeys(fn ($field) => [$field->name => $field->label()])->all();
    }

    public function render()
    {
        return $this->view()->title(__(':name submissions', ['name' => $this->form->name]));
    }
}; ?>

<x-admin.form-screen :form="$form" current="submissions">
    <div class="space-y-4">
        @forelse ($this->submissions as $submission)
            <flux:card wire:key="submission-{{ $submission->id }}" class="space-y-3">
                <div class="flex items-center gap-2">
                    <flux:text size="sm" class="font-medium">{{ $submission->created_at->isoFormat('LLL') }}</flux:text>
                    <flux:spacer />
                    <flux:button size="xs" variant="ghost" icon="trash" wire:click="deleteSubmission({{ $submission->id }})" wire:confirm="{{ __('Delete this submission?') }}" :aria-label="__('Delete')" />
                </div>

                <dl class="grid gap-x-6 gap-y-2 sm:grid-cols-[12rem_1fr]">
                    @foreach ($submission->data as $name => $value)
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ $this->labels[$name] ?? Str::headline($name) }}</dt>
                        <dd class="text-sm whitespace-pre-line text-zinc-800 dark:text-zinc-100">{{ is_bool($value) ? ($value ? __('Yes') : __('No')) : (filled($value) ? $value : '—') }}</dd>
                    @endforeach
                </dl>
            </flux:card>
        @empty
            <flux:text class="py-8 text-center">{{ __('No submissions yet.') }}</flux:text>
        @endforelse

        <flux:pagination :paginator="$this->submissions" />
    </div>
</x-admin.form-screen>
