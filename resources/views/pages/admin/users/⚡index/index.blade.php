<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <x-admin.heading icon="users">{{ __('Users') }}</x-admin.heading>
            <flux:text class="mt-1">{{ __('Everyone with an account.') }}</flux:text>
        </div>

        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search users...')" class="max-w-xs" clearable />
    </div>

    <flux:table :paginate="$this->users">
        <flux:table.columns>
            <x-admin.sortable-column column="name" :direction="$this->sortDirection('name')">{{ __('Name') }}</x-admin.sortable-column>
            <x-admin.sortable-column column="email" :direction="$this->sortDirection('email')">{{ __('Email') }}</x-admin.sortable-column>
            <flux:table.column>{{ __('Role') }}</flux:table.column>
            <x-admin.sortable-column column="created_at" :direction="$this->sortDirection('created_at')">{{ __('Joined') }}</x-admin.sortable-column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->users as $user)
                <flux:table.row :key="$user->id">
                    <flux:table.cell class="flex items-center gap-3">
                        <flux:avatar size="xs" :name="$user->name" :initials="$user->initials()" />
                        <span class="font-medium text-zinc-800 dark:text-white">{{ $user->name }}</span>
                    </flux:table.cell>

                    <flux:table.cell>{{ $user->email }}</flux:table.cell>

                    <flux:table.cell>
                        @foreach ($user->roles as $role)
                            <flux:badge size="sm" :color="$role->name === App\Enums\UserRole::Admin->value ? 'amber' : 'zinc'" inset="top bottom">
                                {{ ucfirst($role->name) }}
                            </flux:badge>
                        @endforeach
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">{{ $user->created_at?->toFormattedDateString() }}</flux:table.cell>

                    <flux:table.cell align="end">
                        @can('impersonate', $user)
                            <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                                @csrf
                                <flux:button type="submit" size="sm" variant="ghost" icon="finger-print" inset="top bottom" data-test="impersonate-button">
                                    {{ __('Impersonate') }}
                                </flux:button>
                            </form>
                        @endcan
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-8 text-center">{{ __('No users found.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>