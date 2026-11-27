<?php

use App\Models\Form;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    public Form $form;

    public string $name = '';

    public string $description = '';

    public string $submitLabel = '';

    public string $successMessage = '';

    public function mount(Form $form): void
    {
        $this->authorize('update', $form);

        $this->form = $form;
        $this->name = $form->name;
        $this->description = $form->description ?? '';
        $this->submitLabel = $form->submit_label ?? '';
        $this->successMessage = $form->success_message ?? '';
    }

    public function save(): void
    {
        $this->authorize('update', $this->form);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'submitLabel' => ['nullable', 'string', 'max:255'],
            'successMessage' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->form->recordingRevision(function (): void {
            $this->form->update([
                'name' => $this->name,
                'description' => $this->description ?: null,
                'submit_label' => $this->submitLabel ?: null,
                'success_message' => $this->successMessage ?: null,
            ]);
        });

        Flux::toast(variant: 'success', text: __('Settings saved.'));
    }

    public function render()
    {
        return $this->view()->title(__(':name settings', ['name' => $this->form->name]));
    }
}; ?>

<x-admin.form-screen :form="$form" current="settings" save="save">
    <form wire:submit="save" class="grid items-start gap-6 lg:grid-cols-3">
        <flux:card class="space-y-6 lg:col-span-2">
            <flux:heading size="lg">{{ __('General') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" :description:trailing="__('Only shown in the admin.')" required />

            <flux:textarea wire:model="description" :label="__('Intro')" :description:trailing="__('Shown above the form.')" rows="4" />
        </flux:card>

        <flux:card class="space-y-6 lg:sticky lg:top-0">
            <div>
                <flux:heading size="lg">{{ __('Submitting') }}</flux:heading>
                <flux:text class="mt-1">{{ __('What visitors see when they send the form.') }}</flux:text>
            </div>

            <flux:input wire:model="submitLabel" :label="__('Submit button')" :placeholder="__('Send')" />

            <flux:textarea wire:model="successMessage" :label="__('Success message')" :placeholder="__('Thank you, we received your message.')" rows="3" />
        </flux:card>

        {{-- Lets Enter submit the form, the visible save button lives in the header bar. --}}
        <button type="submit" class="hidden"></button>
    </form>
</x-admin.form-screen>
