<?php

namespace App\Registry;

use Illuminate\Support\Facades\File;
use ReflectionClass;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Holds every type of one kind (like the brick types), keyed by its key and sorted for the "Add" menu.
 * Subclasses receive their types from the container through #[Tag], see BrickServiceProvider.
 *
 * @template TType of RegisteredType
 */
abstract class TypeRegistry
{
    /**
     * @var array<string, TType>
     */
    protected array $types = [];

    /**
     * @param  iterable<TType>  $types
     */
    public function __construct(iterable $types)
    {
        $types = collect($types)
            ->sortBy(fn (RegisteredType $type) => [$type->order(), $type->label()])
            ->all();

        foreach ($types as $type) {
            $this->types[$type->key()] = $type;
        }
    }

    /**
     * The classes in a directory of app/ that implement the interface, to tag them in the container.
     *
     * @param  class-string  $interface
     * @return array<int, class-string>
     */
    public static function discover(string $directory, string $interface): array
    {
        return collect(File::files(app_path($directory)))
            ->map(fn (SplFileInfo $file) => 'App\\'.str_replace('/', '\\', $directory).'\\'.$file->getFilenameWithoutExtension())
            ->filter(fn (string $class) => is_subclass_of($class, $interface)
                && (new ReflectionClass($class))->isInstantiable())
            ->values()
            ->all();
    }

    /**
     * @return array<string, TType>
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * The types that can be added right now.
     *
     * @return array<string, TType>
     */
    public function available(): array
    {
        return array_filter($this->types, fn (RegisteredType $type) => $type->isAvailable());
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->types);
    }

    /**
     * @return TType|null
     */
    public function find(string $key): ?RegisteredType
    {
        return $this->types[$key] ?? null;
    }
}
