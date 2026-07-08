=== Registry - Gift Registry for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, gift registry, wishlist, wedding, baby shower
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Erfordert Plugins: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ermögliche deinen Kunden, gemeinsam nutzbare Geschenklisten für Hochzeiten, Babypartys und Veranstaltungen zu erstellen, mit Kaufverfolgung, damit Gäste nie doppelt kaufen.

== Description ==

Registry fügt deinem WooCommerce-Shop Geschenklisten hinzu. Ein angemeldeter Kunde erstellt eine benannte Registrierung für eine Veranstaltung (Hochzeit, Babyparty, Geburtstag, Einweihungsfeier oder anderes), wählt die gewünschten Produkte aus dem Shop aus und erhält einen gemeinsam nutzbaren Link, den er an Freunde und Familie senden kann.

Gäste öffnen diesen Link, sehen, welche Artikel noch benötigt werden, und kaufen ein Geschenk. Gekaufte Mengen werden anhand echter WooCommerce-Bestellungen gezählt, sodass alles, was bereits gekauft wurde, als vollständig gekauft markiert wird und zwei Personen nicht zweimal dasselbe kaufen.

Quellen- und Issue-Tracker live auf GitHub: https://github.com/wppoland/registry

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/registry/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/registry/
* <strong>Quellcode</strong> – https://github.com/wppoland/registry
* <strong>Fehlerberichte und Funktionsanfragen</strong> – https://github.com/wppoland/registry/issues
* <strong>Diskussionen und Fragen</strong> – https://github.com/wppoland/registry/discussions


= Features =

* Kunden erstellen und verwalten ihre Registrierungen unter „Mein Konto“ → „Geschenklisten“.
* Jede Registrierung hat einen Veranstaltungstyp (Hochzeit, Babyparty, Geburtstag, Einweihungsfeier, Sonstiges) und ein Veranstaltungsdatum.
* Ein Steuerelement „Zur Geschenkliste hinzufügen“ auf einzelnen Produktseiten mit einer gewünschten Menge pro Artikel.
* Eine öffentliche, schreibgeschützte Registrierungsseite mit eigenem Permalink, die zum Teilen bestimmt ist.
* Gekaufte Mengen werden aus bezahlten WooCommerce-Bestellungen zurückgelesen, sodass die verbleibenden Zählungen ohne manuelle Aktualisierungen aktuell bleiben.
* Optionaler Direktkauf über die geteilte Seite; Wenn diese Option deaktiviert ist, werden Gäste stattdessen zur Produktseite weitergeleitet.
* Bei jeder Aktion wird der Registrierungsbesitz überprüft, sodass ein Kunde niemals die Registrierung eines anderen bearbeiten kann.

== Installation ==

1. Lade das Plugin nach „/wp-content/plugins/registry“ hoch oder installiere es über Plugins → Neu hinzufügen.
2. Aktiviere es. WooCommerce muss installiert und aktiv sein.
3. Registrierungen sind standardmäßig aktiviert. Besuche WooCommerce → Geschenklisten, um sie zu deaktivieren oder auszuwählen, ob Gäste direkt auf der freigegebenen Seite einkaufen können.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Ja. WooCommerce muss installiert und aktiv sein.

= Who can create a registry? =

Jeder eingeloggte Kunde über den Bereich „Mein Konto“ → „Geschenklisten“.

= How does purchase tracking work? =

Wenn ein Geschenk über eine Registrierung gekauft wird, wird die Registrierung, zu der es gehört, in der Bestellposition gespeichert. Wenn die Bestellung bearbeitet oder abgeschlossen wird, wird die Menge zum Kaufzähler der Registrierungsstelle addiert und auf der öffentlichen Seite von dem noch benötigten Betrag abgezogen. Jede Bestellung wird nur einmal gezählt.

= Can guests buy directly from the shared page? =

Ja, wenn in den Einstellungen „Direktkauf zulassen“ aktiviert ist. Andernfalls leitet der Kaufen-Button die Gäste zur Produktseite weiter.

= Do guests need an account to view a shared registry? =

Nein. Die Seite des öffentlichen Registers ist für jeden, der über den Link verfügt, schreibgeschützt; Lediglich zum Erstellen und Bearbeiten von Registern ist ein angemeldeter Kunde erforderlich.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es im Netzwerk oder auf einzelnen Websites. Jede Site behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Im Schaufenster.
2. Einstellungen im WordPress-Admin.
3. Auf einem mobilen Gerät.
== External Services ==

Die Registrierung stellt keine Verbindung zu einem externen Dienst her. Es werden keine ausgehenden Netzwerkanfragen gestellt und keine Daten von deiner Website gesendet. Registrierungen werden in WordPress als benutzerdefinierter Beitragstyp „gift_registry“ mit dem Beitrags-Meta „_registry_*“ (Ereignistyp, Ereignisdatum, ausgewählte Artikel und gekaufte Anzahl) gespeichert und die Einstellungen des Plugins befinden sich in den Optionen „registry_settings“ und „registry_db_version“. Die Kaufverfolgung liest aus deinen eigenen WooCommerce-Bestellungen und zeichnet „_registry_id“, „_registry_purchased“ und „_registry_counted“ in den relevanten Bestellpositionen auf; alles bleibt in deiner Datenbank.

== Changelog ==

= 1.0.1 =
* Erste stabile Version.

= 0.1.4 =
* Die Aktionen „registry/purchase_recorded“ und „registry/thankyou_purchase“ nach bezahlten Registrierungsgeschenken werden gezählt.
* Filter „registry/theme“, „registry/theme_vars“ und Aktion „registry/public_hero“ für PRO-Registrierungsthemen.

= 0.1.3 =
* Füge die Filter „registry/can_manage“, „registry/can_delete“, „registry/user_registries“ und „registry/is_owner“ für gemeinsam genutzte Registrierungen hinzu.
* Füge die Aktion „registry/account/single_registry“, den Filter „registry/account/notices“ und den Meta-Helper „_registry_contributors“ für Listen mit gemeinsamem Besitz hinzu.

= 0.1.2 =
* Registry/max_registries_limit-Filter hinzufügen.
* Füge in „Mein Konto“ Überprüfungen und Hinweise zum Registrierungslimit hinzu.

= 0.1.0 =
* Erstveröffentlichung.
