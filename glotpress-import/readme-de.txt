=== Registry - Gift Registry for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, gift registry, wishlist, wedding, baby shower
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lass deine Kunden teilbare Geschenklisten für Hochzeiten, Babypartys und Events erstellen, mit Kaufverfolgung, damit Gäste nie doppelt kaufen.

== Description ==

Registry fügt deinem WooCommerce-Shop Geschenklisten hinzu. Ein eingeloggter Kunde erstellt eine benannte Liste für ein Event (Hochzeit, Babyparty, Geburtstag, Einweihungsfeier oder anderes), wählt die gewünschten Produkte aus dem Shop und erhält einen teilbaren Link für Freunde und Familie.

Gäste öffnen diesen Link, sehen, welche Artikel noch benötigt werden, und kaufen ein Geschenk. Gekaufte Mengen werden aus echten WooCommerce-Bestellungen gezählt, sodass bereits Gekauftes als vollständig gekauft markiert wird und zwei Personen nicht zweimal dasselbe kaufen.

Quellcode und Issue-Tracker auf GitHub: https://github.com/wppoland/registry

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/registry/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/registry/
* <strong>Quellcode</strong> - https://github.com/wppoland/registry
* <strong>Fehlerberichte und Funktionswünsche</strong> - https://github.com/wppoland/registry/issues
* <strong>Diskussionen und Fragen</strong> - https://github.com/wppoland/registry/discussions


= Features =

* Kunden erstellen und verwalten ihre Listen unter Mein Konto → Geschenklisten.
* Jede Liste hat einen Event-Typ (Hochzeit, Babyparty, Geburtstag, Einweihungsfeier, Sonstiges) und ein Event-Datum.
* Steuerelement «Zur Geschenkliste hinzufügen» auf Einzelproduktseiten mit gewünschter Menge pro Artikel.
* Öffentliche, schreibgeschützte Listenseite mit eigenem Permalink, zum Teilen gedacht.
* Gekaufte Mengen werden aus bezahlten WooCommerce-Bestellungen zurückgelesen, sodass Restzähler ohne manuelle Updates aktuell bleiben.
* Optionaler Direktkauf von der geteilten Seite; wenn aus, werden Gäste zur Produktseite geschickt.
* Jede Aktion prüft den Listenbesitz, sodass ein Kunde niemals die Liste eines anderen bearbeiten kann.

== Installation ==

1. Lade das Plugin nach `/wp-content/plugins/registry` hoch oder installiere es über Plugins → Neu hinzufügen.
2. Aktiviere es. WooCommerce muss installiert und aktiv sein.
3. Listen sind standardmäßig aktiv. Öffne WooCommerce → Geschenklisten, um sie abzuschalten oder zu wählen, ob Gäste direkt von der geteilten Seite kaufen können.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Ja. WooCommerce muss installiert und aktiv sein.

= Who can create a registry? =

Jeder eingeloggte Kunde, im Bereich Mein Konto → Geschenklisten.

= How does purchase tracking work? =

Wird ein Geschenk über eine Liste gekauft, wird die zugehörige Liste in der Bestellposition gespeichert. Erreicht die Bestellung den Status In Bearbeitung oder Abgeschlossen, wird die Menge zum Kaufzähler der Liste addiert und die öffentliche Seite zieht sie vom noch Benötigten ab. Jede Bestellung wird nur einmal gezählt.

= Can guests buy directly from the shared page? =

Ja, wenn «Direktkauf erlauben» in den Einstellungen aktiv ist. Andernfalls schickt der Kaufen-Button Gäste zur Produktseite.

= Do guests need an account to view a shared registry? =

Nein. Die öffentliche Listenseite ist für jeden mit dem Link schreibgeschützt; nur Erstellen und Bearbeiten von Listen erfordert einen eingeloggten Kunden.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es netzwerkweit oder auf einzelnen Websites; jede Website behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Im Shop.
2. Einstellungen im WordPress-Adminbereich.
3. Auf einem mobilen Gerät.
== External Services ==

Registry kontaktiert keinen externen Dienst. Es werden keine ausgehenden Netzwerkanfragen gestellt und keine Daten von deiner Website gesendet. Listen werden in WordPress als eigener Beitragstyp `gift_registry` mit Post-Meta `_registry_*` (Event-Typ, Event-Datum, gewählte Artikel und Kaufzähler) gespeichert, und die Plugin-Einstellungen liegen in den Optionen `registry_settings` und `registry_db_version`. Die Kaufverfolgung liest deine eigenen WooCommerce-Bestellungen und speichert `_registry_id`, `_registry_purchased` und `_registry_counted` in den relevanten Bestellpositionen; alles bleibt in deiner Datenbank.

== Translations ==

Registry enthält deutsche, polnische und spanische Übersetzungen für die Plugin-Oberfläche. Die Textdomain ist `registry`, sodass Sprachpakete von WordPress.org diese mitgelieferten Übersetzungen ebenfalls überschreiben oder erweitern können.

== Changelog ==

= 1.0.2 =
* Mitgelieferte deutsche, polnische und spanische Übersetzungen für die Plugin-Oberfläche hinzugefügt.

= 1.0.1 =
* Erste stabile Version.

= 0.1.4 =
* Aktionen `registry/purchase_recorded` und `registry/thankyou_purchase` nach dem Zählen bezahlter Listengeschenke.
* Filter `registry/theme`, `registry/theme_vars` und Aktion `registry/public_hero` für PRO-Listenthemen.

= 0.1.3 =
* Filter `registry/can_manage`, `registry/can_delete`, `registry/user_registries` und `registry/is_owner` für geteilte Listen.
* Aktion `registry/account/single_registry`, Filter `registry/account/notices` und Meta-Helfer `_registry_contributors` für Mitbesitzer-Listen.

= 0.1.2 =
* Filter `registry/max_registries_limit`.
* Limitprüfungen und Hinweise in Mein Konto.

= 0.1.0 =
* Erstveröffentlichung.
