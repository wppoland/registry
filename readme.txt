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

Registry adds gift registries to your WooCommerce store. Logged-in customers create a named registry for an event — a wedding, baby shower, birthday or housewarming — add the products they would love to receive, and share a clean public link with friends and family.

Guests open the shared link, see exactly what is still needed, and buy a gift directly. Purchased quantities are tracked from real orders, so items that are already covered are marked as fulfilled and nobody double-buys.

= Features =

* Customers create and manage registries under My Account → Gift Registries.
* Event type and event date for every registry.
* Add products to a registry from any product page, with a desired quantity.
* Shareable, public, read-only registry page on a clean permalink.
* Purchased-quantity tracking from WooCommerce orders — remaining counts update automatically.
* Optional direct purchase straight from the shared registry page.
* `[gift_registry id="123"]` shortcode to embed a registry on any page.
* Ownership is enforced on every action; nothing leaks between customers.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/registry`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be installed and active.
3. Visit WooCommerce → Gift Registries to configure the options.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. WooCommerce must be installed and active.

= Who can create a registry? =

Any logged-in customer, from the My Account → Gift Registries area.

= How does purchase tracking work? =

When a gift is bought through a registry, the order line item records which registry it belongs to. Once the order is paid, the purchased quantity is added to the registry so the public page shows how many are still needed.

= Can guests buy directly from the shared page? =

Yes, if "Allow direct purchase" is enabled in the settings. Otherwise the buy button sends guests to the product page.

== Screenshots ==

1. The public, shareable gift registry page with purchase progress.
2. Managing a registry under My Account.
3. The Gift Registries settings page under WooCommerce.

== Changelog ==

= 0.1.0 =
* Initial release.
