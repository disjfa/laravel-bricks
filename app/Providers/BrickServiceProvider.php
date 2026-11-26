<?php

namespace App\Providers;

use App\Bricks\BrickType;
use App\FormFields\FormFieldType;
use App\Registry\TypeRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel's counterpart of Symfony's autoconfigure: tag every class in app/Bricks that implements BrickType
 * and every class in app/FormFields that implements FormFieldType. BrickRegistry and FormFieldRegistry
 * receive them through #[Tag].
 */
class BrickServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(TypeRegistry::discover('Bricks', BrickType::class), BrickType::class);
        $this->app->tag(TypeRegistry::discover('FormFields', FormFieldType::class), FormFieldType::class);
    }
}
