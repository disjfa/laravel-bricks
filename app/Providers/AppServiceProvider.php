<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\MediaPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Pennant\Feature;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureGates();
        $this->configureFeatures();
    }

    /**
     * Features are site-wide switches (see App\Features), so they are checked without a scope
     * instead of per user. Routes of a feature that is turned off do not exist.
     */
    protected function configureFeatures(): void
    {
        Feature::resolveScopeUsing(fn () => null);

        EnsureFeaturesAreActive::whenInactive(fn () => abort(404));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Abilities that do not belong to a single model. Model abilities live in the policies.
     */
    protected function configureGates(): void
    {
        Gate::define('accessAdmin', fn (User $user): bool => $user->isAdmin());
        Gate::define('manageSettings', fn (User $user): bool => $user->isAdmin());

        // The media model lives in the package, so its policy can not be auto-discovered.
        Gate::policy(Media::class, MediaPolicy::class);
    }
}
