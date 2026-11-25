<?php

namespace App\Providers;

use App\Bricks\BrickType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Laravel's counterpart of Symfony's autoconfigure: tag every class in app/Bricks that implements BrickType.
 * BrickRegistry receives them through #[Tag(BrickType::class)].
 */
class BrickServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag($this->discoverBrickTypes(), BrickType::class);
    }

    /**
     * @return array<int, class-string<BrickType>>
     */
    protected function discoverBrickTypes(): array
    {
        return collect(File::files(app_path('Bricks')))
            ->map(fn (SplFileInfo $file) => 'App\\Bricks\\'.$file->getFilenameWithoutExtension())
            ->filter(fn (string $class) => is_subclass_of($class, BrickType::class)
                && (new ReflectionClass($class))->isInstantiable())
            ->values()
            ->all();
    }
}
