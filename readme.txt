=== Registry - Gift Registry for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, gift registry, wishlist, wedding, baby shower
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers create shareable gift registries for weddings, baby showers and events, with purchase tracking so guests never double-buy.

== Description ==

Registry adds gift registries to your WooCommerce store. A logged-in customer creates a named registry for an event (wedding, baby shower, birthday, housewarming or other), picks the products they want from the shop, and gets a shareable link to send to friends and family.

Guests open that link, see which items are still needed, and buy a gift. Purchased quantities are counted from real WooCommerce orders, so anything already bought is marked as fully purchased and two people don't buy the same thing twice.

Source and issue tracker live on GitHub: https://github.com/wppoland/registry

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

== Screenshots ==

1. The public, shareable gift registry page with purchase progress.
2. Managing a registry under My Account.
3. The Gift Registries settings page under WooCommerce.

== Changelog ==

= 0.1.0 =
* Initial release.
