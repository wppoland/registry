=== Registry - Gift Registry for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, gift registry, wishlist, wedding, baby shower
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers create shareable gift registries for weddings, baby showers and events, with purchase tracking so guests never double-buy.

== Description ==

Registry adds gift registries to your WooCommerce store. A logged-in customer creates a named registry for an event (wedding, baby shower, birthday, housewarming or other), picks the products they want from the shop, and gets a shareable link to send to friends and family.

Guests open that link, see which items are still needed, and buy a gift. Purchased quantities are counted from real WooCommerce orders, so anything already bought is marked as fully purchased and two people don't buy the same thing twice.

Source and issue tracker live on GitHub: https://github.com/wppoland/registry

= Documentation and links =

* **Documentation** - https://plogins.com/registry/docs/
* **Plugin page** - https://plogins.com/registry/
* **Source code** - https://github.com/wppoland/registry
* **Bug reports and feature requests** - https://github.com/wppoland/registry/issues
* **Discussions and questions** - https://github.com/wppoland/registry/discussions


= Features =

* Customers create and manage their registries under My Account → Gift Registries.
* Each registry has an event type (wedding, baby shower, birthday, housewarming, other) and an event date.
* An "Add to gift registry" control on single product pages, with a per-item desired quantity.
* A public, read-only registry page on its own permalink, made for sharing.
* Purchased quantities are read back from paid WooCommerce orders, so remaining counts stay current without manual updates.
* Optional direct purchase from the shared page; with it off, guests are sent to the product page instead.
* Every action checks registry ownership, so one customer can never edit another's registry.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/registry`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be installed and active.
3. Registries are on by default. Visit WooCommerce → Gift Registries to turn them off or to choose whether guests can buy directly from the shared page.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. WooCommerce must be installed and active.

= Who can create a registry? =

Any logged-in customer, from the My Account → Gift Registries area.

= How does purchase tracking work? =

When a gift is bought through a registry, the registry it belongs to is stored on the order line item. When that order reaches processing or completed, the quantity is added to the registry's purchased count, and the public page subtracts it from what's still needed. Each order is only counted once.

= Can guests buy directly from the shared page? =

Yes, if "Allow direct purchase" is enabled in the settings. Otherwise the buy button sends guests to the product page.

= Do guests need an account to view a shared registry? =

No. The public registry page is read-only for anyone with the link; only creating and editing registries requires a logged-in customer.


= Does this plugin work on WordPress Multisite? =

Yes. This plugin is compatible with WordPress Multisite. Network activate it or activate it on individual sites; each site keeps its own settings and data.

== Screenshots ==

1. On the storefront.
2. Settings in the WordPress admin.
3. On a mobile device.
== External Services ==

Registry does not connect to any external service. It makes no outbound network requests and sends no data off your site. Registries are stored in WordPress as a `gift_registry` custom post type with `_registry_*` post meta (event type, event date, chosen items and purchased counts), and the plugin's settings live in the `registry_settings` and `registry_db_version` options. Purchase tracking reads from your own WooCommerce orders and records `_registry_id`, `_registry_purchased` and `_registry_counted` on the relevant order line items; everything stays in your database.

== Changelog ==

= 0.1.4 =
* `registry/purchase_recorded` and `registry/thankyou_purchase` actions after paid registry gifts are counted.
* `registry/theme`, `registry/theme_vars` filters and `registry/public_hero` action for PRO registry themes.

= 0.1.3 =
* Add registry/can_manage, registry/can_delete, registry/user_registries and registry/is_owner filters for shared registries.
* Add registry/account/single_registry action, registry/account/notices filter and _registry_contributors meta helper for co-owned lists.

= 0.1.2 =
* Add registry/max_registries_limit filter.
* Add registry limit checks and notices in My Account.

= 0.1.0 =
* Initial release.
