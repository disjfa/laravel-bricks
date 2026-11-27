---
paths:
  - database/migrations/**
  - database/settings/**
---

# Migrations

## Never change a committed migration

Existing installs have already run it and will not see the change, which leaves their schema out of sync with the
code (a missing column only shows up as an error at runtime). Add a new migration that alters the table instead.

## Settings defaults go in a settings migration

A new property on a class in `app/Settings` needs its default in a new file in `database/settings`
(`php artisan make:settings-migration`), and a fallback in the settings class for values that are missing or invalid.
