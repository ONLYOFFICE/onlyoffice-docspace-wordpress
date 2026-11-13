# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

The WordPress plugin "ONLYOFFICE DocSpace" (WordPress.org slug and text domain `onlyoffice-docspace`, main file `onlyoffice-docspace-wordpress.php`). It connects WordPress to an ONLYOFFICE DocSpace portal: it embeds DocSpace in the admin dashboard, links WordPress users to DocSpace accounts and exports them, and adds a Gutenberg block and an `[onlyoffice-docspace]` shortcode that embed DocSpace rooms and files in posts.

The supported WordPress and PHP versions are the `Requires at least` / `Requires PHP` headers in the main plugin file and `readme.txt`. Keep the two identical: WordPress checks only the main file's headers on activation.

Sibling DocSpace integrations on GitHub (`ONLYOFFICE/onlyoffice-docspace-drupal`, `ONLYOFFICE/moodle-mod_onlyofficedocspace`) and the DocSpace JS SDK docs (api.onlyoffice.com) are the references for SDK usage; check them for prior art.

## Branches

- PRs target `develop`. `master` holds released code; merging into `master` tags and builds a release (see `.github/workflows/`).
- Branch names: `<type>/<short-kebab-description>` (`feature/`, `fix/`, `bugfix/`, `chore/`, `ci/`, `docs/`).

## Setup and commands

```bash
composer install                  # dev tools only (phpcs + WPCS, wp-cli, CaptainHook git hooks); no runtime deps
npm ci                            # root: wp-scripts for linting
cd onlyoffice-docspace-wordpress-block && npm ci && npm run build   # block build/ (gitignored); the plugin fails without it
composer phpcs                    # WordPress coding standards; see the script for ignored paths and minimum_wp_version
vendor/bin/phpcs --standard=WordPress <path>   # lint one file (phpcbf to autofix)
npm run lint:js                   # wp-scripts eslint (format:js to autofix)
npm run lint:css                  # wp-scripts stylelint
composer make-pot && composer update-po   # refresh languages/onlyoffice-docspace.pot and every .po
composer translations             # build .mo and JS .json translations (gitignored, built at release)
```

CaptainHook enforces Conventional Commits on `commit-msg` and runs `composer phpcs` on `pre-commit`. CI runs the linters in `.github/workflows/lint.yml`; read it for the PHP and Node versions.

Runtime behaviour depends on a live DocSpace portal and the JS SDK in a browser, so lints don't show that a change works. The WordPress site's origin must be in the portal's *Developer Tools → JavaScript SDK* allow list.

## Structure

- `includes/class-oodsp-plugin.php` — builds the services, registers every hook through `OODSP_Loader` and the `wp_ajax_oodsp_*` actions.
- `includes/client/class-oodsp-docspace-client.php` — the only server-side DocSpace REST client. Throws `OODSP_Docspace_Client_Exception` on any non-200.
- `includes/managers/` — settings (the `oodsp_settings` option) and the shared-group logic.
- `includes/service/class-oodsp-user-service.php` — the WordPress user ↔ DocSpace account link (user meta `docspace_account`).
- `includes/resources/` — shared script/style registrations (`oodsp-main`, `oodsp-client`, `oodsp-ui`, login and error templates) and the `wp.template` HTML.
- `assets-onlyoffice-docspace/js/docspace-integration-sdk.js` — loads the DocSpace JS SDK from the portal (the SDK version is pinned there) and wraps hashing, login and logout.
- `controller/` — AJAX handlers for the system user and the current user's account.
- `pages/` — admin pages (`main` DocSpace manager, `settings`), the Users list integration (`users`), and front-end rendering of the block and shortcode (`public-docspace`).
- `onlyoffice-docspace-wordpress-block/` — Gutenberg block source and `src/block.json`.

## Rules

- Plain DocSpace passwords never reach WordPress. The browser hashes them with the SDK (`createHash` with the portal's hash settings) and only the hash is posted and stored. Keep this for any new form that takes a DocSpace password.
- Call DocSpace from PHP only through `OODSP_Docspace_Client`, and catch `OODSP_Docspace_Client_Exception` (log with `printStackTrace()`). A 401 on a system-user call deletes the stored system user on purpose.
- Every `wp_ajax_oodsp_*` handler checks its nonce (`oodsp_settings_controller` / `oodsp_user_controller`) and the capability it needs.
- WordPress lowercases shortcode attribute names. The render code reads lowercase keys; keep that when adding block attributes.
- The block's saved markup (`src/save.js`) and `block.json` attributes are stored in posts. Changing either without a `deprecated` entry breaks block validation. `fileId`/`roomId` accept number or string on purpose, for blocks saved by older versions.
- Option, user-meta and AJAX action names are stored on user sites; don't rename them without a migration.
- Add user-visible changes to the top of `CHANGELOG.md` under `## Added` or `## Changed`, without a version header; the version is added at release time. Release CI takes the version from the first dotted number in that file.

## Coding practices

- Before writing new code, check whether WordPress core already provides it (HTTP API, options, user meta, list-table hooks, `wp.template`, capabilities). Use public APIs only; avoid functions and classes marked private or `@access private`.
- Write code for the lowest WordPress and PHP versions the plugin declares. Check the `@since` of every core function you use in the WordPress developer reference. Where a newer core function is needed, copy it into `OODSP_Utils`, as `wp_get_admin_notice()` is.
- Sanitize input early (`OODSP_Utils::get_var_from_request()` or `sanitize_*( wp_unslash( ... ) )`), escape output late (`esc_html`, `esc_attr`, `esc_url`, `wp_kses`), including JSON placed in HTML attributes.
- Inject services through constructors, as the existing classes do (`OODSP_Plugin` builds them once). Don't create new service instances inside methods.
- Before using a DocSpace JS SDK method, option or event, check that the SDK version pinned in `docspace-integration-sdk.js` supports it. Before using a DocSpace REST endpoint, check the DocSpace API docs.
- Read and write the `oodsp_settings` option only through `OODSP_Settings_Manager`, and the `docspace_account` user meta only through `OODSP_User_Service`.
- Call AJAX actions from JS only through `oodsp.client`, which adds the nonce. Handlers fail with `wp_send_json_error( array( 'message' => ... ), <HTTP status> )`.
- Load and log in to the DocSpace SDK only through `DocspaceIntegrationSdk` or `oodsp.main.loadDocspace()`; never add another SDK `<script>`.

## Conventions

- PHP follows the WordPress coding standards. Classes are `OODSP_*` in `class-oodsp-*.php` files. Options, AJAX actions and global functions are prefixed `oodsp`; shared script handles are `oodsp-*`, page handles start with `OODSP_PLUGIN_NAME`. Every PHP class file starts with the `ABSPATH` guard.
- Register new hooks in `OODSP_Plugin` through the loader; the hooks in the `OODSP_Users_Page` constructor are an older exception. Admin pages extend `OODSP_Base_Page`, which enqueues `js/index.js` and `css/index.css` from the page folder and includes `views/index.php`.
- Register shared assets in `OODSP_Resource_Registry` and depend on their handles; pass PHP data with `wp_localize_script` (`_oodsp*` globals). Front-end JS hangs off `window.oodsp`.
- Admin JavaScript is jQuery in an IIFE; the block is React with `@wordpress/*` packages built by `wp-scripts`.
- Every translatable string passes the literal text domain `onlyoffice-docspace` (PHP `__()` family, JS `wp.i18n` / `@wordpress/i18n`), except strings WordPress core already translates, which are called without a domain to reuse core's translation. Add a `translators:` comment for placeholders. Scripts with strings need `wp_set_script_translations`. After adding strings, build the block (`make-pot` reads block strings from `build/`), update the `.pot` and every `.po`, and translate them yourself; a human reviewer checks translations.
- PHP files and the block's `src/*.js` carry the GPL license header. Copy it from an existing file of the same kind onto any new one.
- Commit messages follow Conventional Commits: `<type>(<optional scope>): <description>` with a lowercase type (`feat`, `fix`, `refactor`, `docs`, `ci`, `style`, `build`, `chore`).
