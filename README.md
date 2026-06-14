# plugin-template

GitHub **template repo** for a WPPoland storefront FREE plugin. A thin adapter over
`wppoland/storefront-kit`, pre-wired to the reusable CI/release workflows. Spin up a new plugin in
minutes instead of rebuilding CI each time.

## Create a new plugin

> 🔔 **You must create a new repository for each plugin.** FREE → a **public** repo
> `wppoland/<slug>`. PRO → a separate **private** repo `wppoland/<slug>-pro`.

1. **"Use this template" → create `wppoland/<slug>`** (public).
2. **Run the scaffold script** — replaces all tokens and renames `registry.php → <slug>.php`
   (cross-platform; review the diff before committing):
   ```bash
   python3 scripts/init.py restock Restock "Restock" "Back-in-stock notifications for WooCommerce"
   #                        ^slug   ^Namespace ^Name    ^short description
   rm scripts/init.py
   ```
   Tokens it replaces (case-sensitive):

   | Token | Replace with | Example |
   |---|---|---|
   | `registry` | lowercase slug = text-domain = i18n domain | `restock` |
   | `Registry` | PSR-4 PHP namespace | `Restock` |
   | `REGISTRY` (in `define()`) | uppercased namespace | `RESTOCK` |
   | `registry_` | option/meta prefix (slug, dashes→underscores) | `restock_` |
   | `Registry - Gift Registry for WooCommerce` / `Let customers create shareable gift registries for weddings, baby showers and events.` / `Let customers create shareable gift registries for weddings, baby showers and events.` | name + descriptions | … |
3. `composer install` — resolves `wppoland/storefront-kit ^1.0` from VCS (no symlink). Implement
   your adapter in `src/`, wire it in `config/services.php` + `config/hooks.php`.
   *(For local atomic kit+adapter dev, see the kit README's path-override note.)*
4. Add repo secrets: **`WPORG_SVN_USERNAME`**, **`WPORG_SVN_PASSWORD`**.
5. Drop wp.org assets in `.wordpress-org/`; fill in `readme.txt`.
6. Add a `PluginEntry` to `plogins` `packages/registry/src/plugins.config.ts` + a docs folder.
7. **Release:** bump the header `Version:` + `readme.txt` Stable tag, tag `vX.Y.Z`, push →
   `_release-free.yml` runs CI, vendors the kit, and auto-deploys to wp.org SVN.

## What's wired

- **Bootstrap** (`registry.php`): PHP/WC guards, HPOS + cart-blocks compat, `init` priority 0
  boot, `do_action('registry/booted')` fired from `Plugin::boot()` (the hook a PRO companion extends).
- **Autoload** (`autoload.php`): Composer vendor autoloader + PSR-4 fallback (incl. the kit).
- **DI**: `src/Plugin.php` singleton + `src/Container.php`; services in `config/services.php`,
  boot order in `config/hooks.php`, defaults in `config/defaults.php`; `src/Migrator.php`.
- **CI/Release**: `.github/workflows/{ci,release}.yml` call `wppoland/workflows@v1`.
- **Quality**: `phpcs.xml.dist` (WPCS), `phpstan.neon.dist` (level 6 + WC stubs), `.distignore`
  (ships `vendor/` so the kit travels), `.wp-env.json`.

## PRO companion (`<slug>-pro`, private)

Create a separate private repo. It hooks `add_action('<slug>/booted', …)`, bundles the Freemius
SDK, and releases via `wppoland/workflows/.github/workflows/_release-pro.yml@v1`.
