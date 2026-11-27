# Bricks

A small CMS built with Laravel, Livewire and Flux. You build pages and blogs from bricks (headings, text, images,
forms, buttons and more), with a live preview next to the editor.

- **Pages and blogs** built from bricks, with categories and tags for blogs
- **Form builder**, with submissions in the admin and spam protection (Cloudflare Turnstile)
- **Menu builder** for the header and footer menus
- **Media library** for images and files
- **Site settings**: title, logo, SEO, installable app (PWA), fonts, theme colors and corners
- **Content API**: read-only, with tokens and OpenAPI docs
- **Modules**: blogs, forms and the site menu can be turned off on the settings page

## Requirements

- PHP 8.3 or newer (tested on 8.5)
- Composer
- Node.js 22 or newer with npm
- A database: SQLite works out of the box, MySQL/MariaDB and PostgreSQL work too

## Installation

```shell
git clone <repository-url> bricks
cd bricks
composer setup
```

`composer setup` installs the dependencies, creates `.env` with an app key, runs the migrations, links the storage
folder and builds the assets. By default it uses SQLite (`database/database.sqlite`); for another database, create
`.env` from `.env.example` with your database settings first.

Then seed the database. This creates the roles, an admin account and example content:

```shell
php artisan db:seed
```

Start the app with `composer dev` (or point your web server at `public/`) and open http://localhost:8000.

Sign in at `/login` with **admin@example.com** and the password **password**, and change that password under your
profile settings. The admin is at `/admin`.

## Configuration

Everything is set in `.env`:

| Setting | What it does |
| --- | --- |
| `DB_*` | The database |
| `CACHE_STORE`, `QUEUE_CONNECTION`, `SESSION_DRIVER` | Defaults to the database and the sync queue; use Redis where you have it |
| `MAIL_*` | Mail for password resets and email verification |
| `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` | Spam protection for website forms (optional) |

With a queue other than `sync`, run a worker (`php artisan queue:work`) to make image thumbnails.

## Working on Bricks

```shell
composer dev     # the app, the queue, the logs and Vite together
composer test    # code style (Pint), static analysis (PHPStan) and the tests (Pest)
composer lint    # fix the code style
```

### With an AI coding agent

The project uses [Laravel Boost](https://laravel.com/docs/boost) to give AI agents the same rules:

- `.ai/guidelines/` holds the project guidelines: how Bricks is built and the choices behind it.
- `.ai/rules/` holds rules for specific folders, with `.ai/rules/index.md` mapping folders to rules.
- `boost.json` lists the agents, packages and skills Boost sets up.

`AGENTS.md` is generated from those files and committed, so every agent that opens the repository reads the rules.
`composer setup` runs `php artisan boost:update`, which also writes the skills and the MCP server config for your
machine; those are not committed. To set it up for an agent that is not in `boost.json` yet, run
`php artisan boost:install`.

To change a rule, edit the files in `.ai/`, run `php artisan boost:update` (and `php artisan boost:index-rules` after
adding a rule file) and commit the updated `AGENTS.md` with it.
