# CLAUDE.md

Guidance for Claude Code when working in this repository.

## What this is

`onlyoffice-docspace-wordpress` — a WordPress plugin (text domain `onlyoffice-docspace`, PHP class prefix `OODSP_`) that embeds ONLYOFFICE DocSpace into WordPress: a DocSpace admin menu page, a Gutenberg block/shortcode for embedding rooms and files, and export of WordPress users into DocSpace.

## Commands

```bash
# PHP lint (WordPress Coding Standards) — also runs as a pre-commit hook
composer phpcs
vendor/bin/phpcbf --standard=Wordpress <file>   # autofix

# JS/CSS lint + format (run from the repo root)
npm run lint:js
npm run lint:css
npm run format:js

# Build the Gutenberg block (separate npm package)
cd onlyoffice-docspace-wordpress-block && npm install && npm run build
cd onlyoffice-docspace-wordpress-block && npm run start   # watch mode

# Translations (wp-cli comes from composer require-dev)
composer make-pot        # regenerate languages/onlyoffice-docspace.pot
composer update-po       # merge the .pot into the .po files
composer translations    # compile .mo + per-script .json
```

`composer phpcs` uses the `Wordpress` standard and skips `assets-onlyoffice-docspace/`, `onlyoffice-docspace-wordpress-block/`, `vendor/`, and `node_modules/`. CI ([.github/workflows/lint.yml](.github/workflows/lint.yml)) runs the JS/CSS lints first, then phpcs.

## Architecture

**Bootstrap.** [onlyoffice-docspace-wordpress.php](onlyoffice-docspace-wordpress.php) defines the `OODSP_*` constants, registers activate/deactivate/uninstall hooks, and constructs `OODSP_Plugin`. `OODSP_Plugin::__construct()` requires every class file, builds the four long-lived services, then registers everything through `OODSP_Loader` (a deferred `add_action`/`add_filter` queue flushed by `run()`).

**Services** (constructed once, injected everywhere):

| Class | Role |
|---|---|
| `OODSP_Settings_Manager` | all reads/writes of the single `oodsp_settings` option |
| `OODSP_User_Service` | the `docspace_account` user meta |
| `OODSP_Docspace_Client` | HTTP calls to the DocSpace REST API (`/api/2.0/...`) |
| `OODSP_DocSpace_Action_Manager` | higher-level DocSpace operations (the shared group) |

**Directories.**

- `includes/` — services, models, `OODSP_Utils`, `OODSP_Docspace_Client_Exception`, and `includes/resources/` (the shared JS/CSS/images plus `OODSP_Resource_Registry` and `OODSP_Templates`).
- `pages/` — one directory per admin screen, each with `class-oodsp-*-page.php` + `views/`, `js/`, `css/`. Screens extending [OODSP_Base_Page](pages/class-oodsp-base-page.php) get `add_submenu_page`, automatic enqueue of their own `js/index.js` and `css/index.css`, and rendering of `views/index.php`. `OODSP_Users_Page` and `OODSP_Public_DocSpace_Page` do *not* extend it — they hook into existing WordPress screens instead.
- `controller/` — `wp_ajax_*` handlers only. Every method starts with `check_ajax_referer()` and replies via `wp_send_json_error()`.
- `onlyoffice-docspace-wordpress-block/` — a self-contained npm package for the Gutenberg block (its own `package.json`, `.eslintrc`, and pinned `@wordpress/scripts`, different from the root one). `src/` is the source; `build/` is generated and gitignored but ships in the release zip.
- `assets-onlyoffice-docspace/js/docspace-integration-sdk.js` — a vendored copy of the DocSpace JS SDK loader (exposes `window.DocspaceIntegrationSdk`, pulls `static/scripts/sdk/<version>/api.js` from the configured portal). Excluded from lint and formatting; treat it as third-party.

**Frontend assets.** `OODSP_Resource_Registry::register_resources()` (on `init`) `wp_register_script`s the shared handles — `oodsp-main`, `oodsp-client`, `oodsp-ui`, `oodsp-login-page-template`, `oodsp-error-page-template`, `docspace-integration-sdk` — and localizes the `_oodsp*` globals. Pages then just declare these handles as dependencies. Browser code attaches to a single `window.oodsp` namespace (`oodsp.main`, `oodsp.client`, `oodsp.ui`, `oodsp.templates`); `oodsp.client` is the only place that talks to `admin-ajax.php`. `OODSP_Templates` prints `wp.template` (`<script type="text/html" id="tmpl-...">`) markup on `admin_footer`/`wp_footer`.

## Persistence and auth model

Two distinct DocSpace identities, easy to confuse:

- **System user** — one DocSpace *administrator*, stored in `oodsp_settings['system_user']` (id, user name, password hash, `asc_auth_key` token). `OODSP_Docspace_Client` attaches its token as a cookie to every server-side request unless one is passed explicitly. A 401 from DocSpace deletes it automatically. It gates user export and the shared group.
- **Per-user DocSpace account** — `docspace_account` user meta (id, user name, password hash) for each WordPress user. Used client-side: the password hash is handed to the SDK's `loginByPasswordHash` so the iframe logs in seamlessly; if that fails the JS falls back to the in-frame login template.

Everything else lives in the `oodsp_settings` option: `docspace_url` and `shared_group` (the id of the `WordPress Users (<site name>)` DocSpace group). Disconnecting resets the whole option and drops the meta for all users. There are no custom database tables — activation only drops a legacy `{prefix}docspace_users` table left by 1.x.

## Conventions

- WordPress Coding Standards throughout: tabs, Yoda conditions, `snake_case` methods, one class per file named `class-oodsp-<thing>.php`, full docblocks on every class/method/property (phpcs enforces them).
- Every PHP file opens with the GPL-2.0 header block and an `if ( ! defined( 'ABSPATH' ) ) { exit; }` guard. JS source files carry the same header (the vendored SDK and generated `build/` aside).
- All user-facing strings go through `__()`/`esc_html_e()`/`wp.i18n.__()` with the `onlyoffice-docspace` text domain; scripts with strings also need `wp_set_script_translations()`.
- The code targets **PHP 8.0+** — union return types (`OODSP_System_User|null`), typed properties, named arguments, trailing commas in parameter lists.
- Errors from the DocSpace API surface as `OODSP_Docspace_Client_Exception`. Either convert it into a user-facing notice, or swallow it with `$e->printStackTrace()` (which only logs when `WP_DEBUG` is on) — never let it escape into a hook callback.
- Reading request data goes through `OODSP_Utils::get_var_from_request()`, which unslashes and sanitizes.

## Release mechanics

Commits must follow Conventional Commits — `captainhook.json` wires a `commit-msg` validator and a `pre-commit` `composer phpcs`. Work branches off `develop`; `master` is the release branch.

A version bump touches four places, and they must agree:

1. `CHANGELOG.md` — the first `## <version>` heading is what [create-tag.yml](.github/workflows/create-tag.yml) greps to create the `v*` tag, and [release.yml](.github/workflows/release.yml) slices the same file for the release notes.
2. [onlyoffice-docspace-wordpress.php](onlyoffice-docspace-wordpress.php) — the `Version:` header *and* the `OODSP_VERSION` constant (which also cache-busts every enqueued asset).
3. `readme.txt` — `Stable tag`.
4. `onlyoffice-docspace-wordpress-block/package.json` — `version`.

The release job builds the block, compiles translations, then strips `vendor/`, `node_modules/`, both `package.json` files, `composer.*`, `captainhook.json`, `README.md`, `.github/`, and `.gitignore` before zipping. So `build/`, `languages/*.mo` and `languages/*.json` are gitignored yet required in the shipped artifact — regenerate them locally when testing an installed copy.
