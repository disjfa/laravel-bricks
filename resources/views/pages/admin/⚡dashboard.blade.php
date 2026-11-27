<?php

use App\Models\Page;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::admin'), Title('Admin')] class extends Component {
    /**
     * @return array<int, array{label: string, value: int, icon: string, url: string|null}>
     */
    #[Computed]
    public function stats(): array
    {
        return [
            [
                'label' => __('Pages'),
                'value' => Page::query()->count(),
                'icon' => 'document-text',
                'url' => auth()->user()->can('viewAny', Page::class) ? route('admin.pages.index') : null,
            ],
            [
                'label' => __('Published pages'),
                'value' => Page::query()->published()->count(),
                'icon' => 'globe-alt',
                'url' => null,
            ],
            [
                'label' => __('Users'),
                'value' => User::query()->count(),
                'icon' => 'users',
                'url' => auth()->user()->can('viewAny', User::class) ? route('admin.users.index') : null,
            ],
        ];
    }
}; ?>

<div class="space-y-6">
    <div>
        <x-admin.heading icon="home">{{ __('Dashboard') }}</x-admin.heading>
        <flux:text class="mt-1">{{ __('Welcome back, :name.', ['name' => auth()->user()->name]) }}</flux:text>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        @foreach ($this->stats as $stat)
            <flux:card wire:key="stat-{{ $loop->index }}" class="flex items-center gap-4">
                <div class="rounded-lg bg-zinc-100 p-3 dark:bg-zinc-700">
                    <flux:icon :name="$stat['icon']" class="size-6" />
                </div>

                <div>
                    <flux:text>{{ $stat['label'] }}</flux:text>
                    <flux:heading size="xl">{{ $stat['value'] }}</flux:heading>
                </div>

                @if ($stat['url'])
                    <flux:spacer />
                    <flux:button variant="ghost" size="sm" icon="arrow-right" :href="$stat['url']" wire:navigate />
                @endif
            </flux:card>
        @endforeach
    </div>
</div>
