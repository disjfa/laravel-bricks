<?php

namespace App\Providers;

use App\Bricks\BrickType;
use App\FormFields\FormFieldType;
use App\MenuLinks\MenuLinkType;
use App\Registry\TypeRegistry;
use Illuminate\Support\ServiceProvider;

/**
 * Laravel's counterpart of Symfony's autoconfigure: tag every class in app/Bricks that implements BrickType,
 * in app/FormFields that implements FormFieldType and in app/MenuLinks that implements MenuLinkType.
 * BrickRegistry, FormFieldRegistry and MenuLinkRegistry receive them through #[Tag].
 */
class BrickServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(TypeRegistry::discover('Bricks', BrickType::class), BrickType::class);
        $this->app->tag(TypeRegistry::discover('FormFields', FormFieldType::class), FormFieldType::class);
        $this->app->tag(TypeRegistry::discover('MenuLinks', MenuLinkType::class), MenuLinkType::class);
    }
}
