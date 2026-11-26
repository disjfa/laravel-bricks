<?php

use App\Enums\ApiAbility;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin'), Title('API tokens')] class extends Component {
    public string $name = '';

    /**
     * The secret of the token that was just made. Sanctum only stores a hash, so it is shown this once.
     */
    public ?string $plainTextToken = null;

    public function mount(): void
    {
        $this->authorize('manageApiTokens');
    }

    public function createToken(): void
    {
        $this->authorize('manageApiTokens');

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->plainTextToken = auth()->user()->createToken($this->name, [ApiAbility::ContentRead->value])->plainTextToken;

        $this->reset('name');
        unset($this->tokens);

        Flux::modal('create-token')->close();
        Flux::modal('new-token')->show();
    }

    public function revokeToken(int $tokenId): void
    {
        $this->authorize('manageApiTokens');

        auth()->user()->tokens()->whereKey($tokenId)->firstOrFail()->delete();

        unset($this->tokens);

        Flux::toast(variant: 'success', text: __('Token revoked.'));
    }

    /**
     * @return Collection<int, PersonalAccessToken>
     */
    #[Computed]
    public function tokens(): Collection
    {
        return auth()->user()->tokens()->latest('id')->get();
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('API tokens') }}</flux:heading>
            <flux:text class="mt-1">
                {{ __('Tokens give read access to the published content over the API. Send one as a bearer token.') }}
                <flux:link :href="route('scramble.docs.ui')" target="_blank">{{ __('API documentation') }}</flux:link>
            </flux:text>
        </div>

        <flux:modal.trigger name="create-token">
            <flux:button variant="primary" icon="plus" data-test="new-token-button">{{ __('New token') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Last used') }}</flux:table.column>
            <flux:table.column>{{ __('Created') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->tokens as $token)
                <flux:table.row :key="$token->id">
                    <flux:table.cell variant="strong">{{ $token->name }}</flux:table.cell>
                    <flux:table.cell>{{ $token->last_used_at?->diffForHumans() ?? __('Never') }}</flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">{{ $token->created_at?->isoFormat('LL') }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="trash"
                            inset="top bottom"
                            wire:click="revokeToken({{ $token->id }})"
                            wire:confirm="{{ __('Revoke this token? Apps that use it lose access right away.') }}"
                            :aria-label="__('Revoke')"
                        />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="py-8 text-center">{{ __('No tokens yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="create-token" :show="$errors->has('name')" class="max-w-lg">
        <form wire:submit="createToken" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('New token') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Name it after the app that uses it, so you know what to revoke later.') }}</flux:text>
            </div>

            <flux:input wire:model="name" :label="__('Name')" :placeholder="__('Mobile app')" required autofocus />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary">{{ __('Create token') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="new-token" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Copy your token') }}</flux:heading>
                <flux:text class="mt-2">{{ __('This is the only time it is shown. Store it somewhere safe.') }}</flux:text>
            </div>

            @if ($plainTextToken)
                <flux:input :value="$plainTextToken" readonly copyable class:input="font-mono" data-test="plain-text-token" />
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="primary">{{ __('Done') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
