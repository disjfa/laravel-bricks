<?php

namespace App\Bricks;

use App\Enums\BrickWidth;
use App\Enums\TextAlign;
use App\Fields\Field;
use App\Fields\FieldSet;
use App\Fields\Select;
use Illuminate\Validation\Validator;

/**
 * Derives the defaults, validation and view of a brick from its fields, and gives every brick
 * the same settings (like its width). Override extraSettings() to give a brick settings of its own.
 */
abstract class BaseBrick implements BrickType
{
    public function order(): int
    {
        return 100;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function defaults(): array
    {
        return FieldSet::of($this->fields())->defaults();
    }

    public function rules(): array
    {
        return FieldSet::of($this->fields())->rules();
    }

    public function validator(array $data): Validator
    {
        return FieldSet::of($this->fields())->validator($data);
    }

    /**
     * The settings every brick has, like its width. A brick adds its own with extraSettings().
     */
    public function settings(): array
    {
        return [
            Select::make('width')
                ->label(__('Width'))
                ->required()
                ->segmented()
                ->options(collect(BrickWidth::cases())->mapWithKeys(fn (BrickWidth $width) => [$width->value => $width->label()])->all()),
            ...$this->extraSettings(),
        ];
    }

    /**
     * @return array<int, Field>
     */
    protected function extraSettings(): array
    {
        return [];
    }

    /**
     * The alignment setting for bricks with text, read on the website with TextAlign::fromSettings().
     */
    protected function alignSetting(): Select
    {
        return Select::make('align')
            ->label(__('Alignment'))
            ->required()
            ->segmented()
            ->options(collect(TextAlign::cases())->mapWithKeys(fn (TextAlign $align) => [$align->value => $align->label()])->all());
    }

    public function settingsDefaults(): array
    {
        return FieldSet::of($this->settings())->defaults();
    }

    public function settingsValidator(array $settings): Validator
    {
        return FieldSet::of($this->settings())->validator($settings);
    }

    public function view(): string
    {
        return "bricks.web.{$this->key()}";
    }
}
