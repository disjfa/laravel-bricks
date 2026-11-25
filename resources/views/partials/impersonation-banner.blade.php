@impersonating
    <div class="flex items-center justify-center gap-3 bg-amber-400 px-4 py-2 text-sm font-medium text-amber-950" data-test="impersonation-banner">
        <span>{{ __('You are impersonating :name.', ['name' => auth()->user()->name]) }}</span>

        <form method="POST" action="{{ route('impersonation.destroy') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="underline underline-offset-2 hover:no-underline">
                {{ __('Leave impersonation') }}
            </button>
        </form>
    </div>
@endImpersonating
