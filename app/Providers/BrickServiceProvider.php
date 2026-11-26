<?php

namespace App\Providers;

use App\Bricks\BrickType;
use App\FormFields\FormFieldType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Laravel's counterpart of Symfony's autoconfigure: tag every class in app/Bricks that implements BrickType
 * and every class in app/FormFields that implements FormFieldType. BrickRegistry and FormFieldRegistry
 * receive them through #[Tag].
 */
class BrickServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag($this->discover('Bricks', BrickType::class), BrickType::class);
        $this->app->tag($this->discover('FormFields', FormFieldType::class), FormFieldType::class);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $interface
     * @return array<int, class-string<T>>
     */
    protected function discover(string $directory, string $interface): array
    {
        return collect(File::files(app_path($directory)))
            ->map(fn (SplFileInfo $file) => "App\\{$directory}\\".$file->getFilenameWithoutExtension())
            ->filter(fn (string $class) => is_subclass_of($class, $interface)
                && (new ReflectionClass($class))->isInstantiable())
            ->values()
            ->all();
    }
}
