<?php

namespace App\FormFields;

use Illuminate\Container\Attributes\Singleton;
use Illuminate\Container\Attributes\Tag;

/**
 * Holds every available form field type, keyed by its key and sorted for the "Add field" menu.
 * The container injects everything tagged as a FormFieldType, see BrickServiceProvider.
 */
#[Singleton]
class FormFieldRegistry
{
    /**
     * @var array<string, FormFieldType>
     */
    protected array $types = [];

    /**
     * @param  iterable<FormFieldType>  $types
     */
    public function __construct(#[Tag(FormFieldType::class)] iterable $types)
    {
        $types = collect($types)
            ->sortBy(fn (FormFieldType $type) => [$type->order(), $type->label()])
            ->all();

        foreach ($types as $type) {
            $this->types[$type->key()] = $type;
        }
    }

    /**
     * @return array<string, FormFieldType>
     */
    public function all(): array
    {
        return $this->types;
    }

    /**
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->types);
    }

    public function find(string $key): ?FormFieldType
    {
        return $this->types[$key] ?? null;
    }
}
