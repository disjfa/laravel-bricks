<?php

use App\Models\Form;
use Illuminate\Support\Facades\Validator;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::admin')] class extends Component {
    public Form $form;

    public string $name = '';

    public string $description = '';

    public string $submitLabel = '';

    public string $successMessage = '';

    /**
     * One or more addresses, separated by commas. Empty sends submissions to the admins.
     */
    public string $notificationEmail = '';

    public function mount(Form $form): void
    {
        $this->authorize('update', $form);

        $this->form = $form;
        $this->name = $form->name;
        $this->description = $form->description ?? '';
        $this->submitLabel = $form->submit_label ?? '';
        $this->successMessage = $form->success_message ?? '';
        $this->notificationEmail = $form->notification_email ?? '';
    }

    public function save(): void
    {
        $this->authorize('update', $this->form);

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'submitLabel' => ['nullable', 'string', 'max:255'],
            'successMessage' => ['nullable', 'string', 'max:2000'],
            'notificationEmail' => ['nullable', 'string', 'max:1000', function (string $attribute, string $value, Closure $fail): void {
                $addresses = array_filter(array_map('trim', explode(',', $value)));

                if (Validator::make(['addresses' => $addresses], ['addresses.*' => ['email']])->fails()) {
                    $fail(__('Use email addresses separated by commas, like info@example.com, sales@example.com.'));
                }
            }],
        ], attributes: ['notificationEmail' => __('send submissions to')]);

        $this->form->recordingRevision(function (): void {
            $this->form->update([
                'name' => $this->name,
                'description' => $this->description ?: null,
                'submit_label' => $this->submitLabel ?: null,
                'success_message' => $this->successMessage ?: null,
                'notification_email' => $this->notificationEmail ?: null,
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

            <flux:input
                wire:model="notificationEmail"
                type="text"
                :label="__('Send submissions to')"
                :placeholder="__('The admins')"
                :description:trailing="__('Every submission is emailed here. Separate addresses with commas. Empty sends it to the admins.')"
                data-test="notification-email"
            />
        </flux:card>

        {{-- Lets Enter submit the form, the visible save button lives in the header bar. --}}
        <button type="submit" class="hidden"></button>
    </form>
</x-admin.form-screen>
