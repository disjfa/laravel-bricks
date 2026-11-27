# Bricks

Bricks is a small CMS: pages and blogs built from bricks, a form builder, a menu builder, a media library, site
settings (title, SEO, PWA, fonts, theme) and a read-only content API. It should run as is after `composer setup`,
so keep it free of machine-specific setup and hardcoded infrastructure.

## Where things live

- Content types that plug into an editor are classes, discovered by `BrickServiceProvider`: bricks in `app/Bricks`,
  form fields in `app/FormFields`, menu links in `app/MenuLinks`. A new class there is registered automatically.
- The fields that edit a type's data and settings are in `app/Fields`, with their admin views in `resources/views/fields`.
- Admin screens are Livewire page components in `resources/views/pages/admin` (`pages::admin.*`), routed with
  `Route::livewire()` in `routes/web.php`. The website uses plain controllers and `resources/views/site`.
- Modules that can be turned off (blogs, forms, site menu) are Pennant features in `app/Features`, listed in
  `SiteFeature::All` and switched on the settings page.
- Site settings are `spatie/laravel-settings` classes in `app/Settings`, with their defaults in `database/settings`.
- The filters and sorts shared by the API and the admin lists are in `app/Queries`.

## Architecture choices

- **Spatie packages first.** When a feature needs a package, use the Spatie one if it fits; the project already
  uses laravel-medialibrary, -settings, -tags, -menu, -permission, -query-builder and -sluggable. Do not add a
  package without asking.
- **Interfaces, not enums, for kinds of things.** Something that has several kinds with their own behavior or fields
  (bricks, form fields, menu link types) is an interface with one class per kind, autowired into a registry with
  `#[Tag]` (like Symfony's `AutoconfigureTag`), see `App\Registry\TypeRegistry`. Never a big enum with a `match` per
  kind. Enums are only for a fixed list of values, like `BrickWidth` or `PublishStatus`.
- **Menus with spatie/laravel-menu.** Every menu, in the admin and on the website, is built with `Spatie\Menu` and
  `App\Menu\MenuLink` (macros in `MenuServiceProvider`), not with arrays of links in a view.
- **Website and admin stay separate.** The website (frontend) is `resources/views/site` with plain controllers and
  `<x-site-layout>`; the admin (backend) is Livewire pages in `resources/views/pages/admin` with
  `layouts/admin.blade.php`. They share only the theme and the brick views (which render the live preview).
- **Views only render.** Blade components and layouts get everything they show handed to them (see
  `App\View\Components\SiteLayout`); no queries or settings lookups in `@php` blocks.
- **The same editor everywhere.** Pages, blogs and forms use the side-by-side editor with a live preview, through
  the shared `<x-admin.screen>` layout, so editing works the same in every section.
- **Forms validate on the server.** Website forms use `novalidate` and show the server-side errors only.

## Working agreements

- Cache, queue and session drivers are an `.env` choice (Redis where available, the database by default). Write
  code that works with any driver: use the default store, `Cache::lock()`, and no driver-specific features.
- Never edit a migration that has already been committed: existing databases have run it and will not pick up the
  change. Add a new migration (or settings migration) instead.
- After changing Flux or Blade components and not seeing it, run `php artisan view:clear`: Blaze folds some Flux
  components at compile time. After CSS or JS changes, run `npm run build` when the Vite dev server is not running.
- Admin and website share the theme: colors, fonts and corners from the settings page are CSS custom properties on
  `[data-site-theme]` (`resources/views/partials/site-theme.blade.php`).

## Planned, not started

Revisions with revert for pages, blogs, forms and menus: one generic setup that stores a full JSON snapshot (made
with API resources) of the model and its ordered children after every save, logged per model. Do not start it
unless asked.
