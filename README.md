# Bricks

A small CMS built with Laravel, Livewire and Flux. You build pages and blogs from bricks (headings, text, images,
forms, buttons and more), with a live preview next to the editor.

- **Pages and blogs** built from bricks, with categories and tags for blogs
- **Form builder**, with submissions in the admin and by email, and spam protection (Cloudflare Turnstile)
- **Menu builder** for the header and footer menus
- **Media library** for images and files
- **Revisions** of pages, blogs and forms: see every saved version and revert to it
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
| `MAIL_*` | Mail for form submissions, password resets and email verification |
| `TURNSTILE_SITE_KEY`, `TURNSTILE_SECRET_KEY` | Spam protection for website forms (optional) |

With a queue other than `sync`, run a worker (`php artisan queue:work`) to make image thumbnails and send the
submission emails.

Each form emails its submissions to the addresses on its Settings tab, or to the admins when it has none. To see those
emails locally, run [Mailpit](https://mailpit.axllent.org) and set `MAIL_MAILER=smtp` and `MAIL_PORT=1025`; the
mails show up at http://localhost:8025.

## Deploying

Bricks deploys with [Deployer](https://deployer.org), with zero downtime: every deploy is a new release folder that
goes live with a symlink switch. The server needs PHP, Composer and git, and SSH access with your key. It clones the repository itself: set
`DEPLOY_REPOSITORY` to the https URL of a public repository, or give the server a read-only deploy key for a private one. The assets are
built on your machine and uploaded, so the server does not need Node.

1. Set the server in your `.env`:

   ```ini
   DEPLOY_HOST=example.com
   DEPLOY_USER=deploy
   DEPLOY_PATH=/var/www/bricks
   ```

   `DEPLOY_BRANCH` defaults to `main` and `DEPLOY_REPOSITORY` to your git remote. Use `DEPLOY_PHP` when the server
   needs a specific PHP binary, like `/usr/bin/php8.5`.

2. Deploy with `vendor/bin/dep deploy`.

3. The first deploy creates `shared/.env` on the server from `.env.example`. Edit it right away for production
   (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, the database and mail), generate a key with
   `vendor/bin/dep artisan:key:generate`, and deploy again. Point the web server at `current/public`.

Every deploy installs the Composer packages, uploads the assets, links the storage, caches the config, routes and
views, runs the migrations, switches to the new release and reloads the queue workers. `.env`, `storage` and an
SQLite database are kept between releases. Roll back with `vendor/bin/dep rollback`.

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
