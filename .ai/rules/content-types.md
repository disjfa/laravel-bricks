---
paths:
  - app/Bricks/**
  - app/FormFields/**
  - app/MenuLinks/**
  - app/Fields/**
  - resources/views/bricks/**
  - resources/views/fields/**
  - resources/views/form-fields/**
---

# Content types: bricks, form fields, menu links

## Add a type by adding a class

A class in `app/Bricks` (implementing `BrickType`), `app/FormFields` (`FormFieldType`) or `app/MenuLinks`
(`MenuLinkType`) is discovered and registered by `BrickServiceProvider`. Extend the base class (`BaseBrick`,
`BaseFormField`, `BaseMenuLink`) and do not register it anywhere by hand.

## Describe data and settings with fields

A type's content comes from `fields()` and its presentation from `settings()`; defaults and validation are derived
from those `App\Fields` fields. Add brick settings with `extraSettings()` so every brick keeps the shared width
setting. Use `Select::segmented()` for a few short options, and reuse `BaseBrick::alignSetting()` for text alignment.

## A brick's website view

A brick renders `resources/views/bricks/web/{key}.blade.php` with `$data`, `$settings` and `$width`. Read settings
through their enum (`BrickWidth::fromSettings()`, `TextAlign::fromSettings()`), so bricks saved before a setting
existed fall back to its default. The same view renders the live preview in the admin.
