<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Lab404\Impersonate\Services\ImpersonateManager;

class ImpersonationsController extends Controller
{
    public function store(Request $request, User $user, ImpersonateManager $impersonateManager): RedirectResponse
    {
        abort_if($impersonateManager->isImpersonating(), 403);

        Gate::authorize('impersonate', $user);

        $request->user()->impersonate($user);

        return redirect()->route('home');
    }

    public function destroy(ImpersonateManager $impersonateManager): RedirectResponse
    {
        abort_unless($impersonateManager->isImpersonating(), 403);

        $impersonateManager->leave();

        return redirect()->route('admin.users.index');
    }
}
