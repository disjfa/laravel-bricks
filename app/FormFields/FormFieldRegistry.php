<?php

namespace App\FormFields;

use App\Registry\TypeRegistry;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Container\Attributes\Tag;

/**
 * Every form field type in app/FormFields, see BrickServiceProvider.
 *
 * @extends TypeRegistry<FormFieldType>
 */
#[Singleton]
class FormFieldRegistry extends TypeRegistry
{
    /**
     * @param  iterable<FormFieldType>  $types
     */
    public function __construct(#[Tag(FormFieldType::class)] iterable $types)
    {
        parent::__construct($types);
    }
}
