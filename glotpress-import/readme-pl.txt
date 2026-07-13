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

Pozwól klientom tworzyć listy prezentów do udostępniania na wesela, baby shower i inne wydarzenia, ze śledzeniem zakupów, aby goście nigdy nie kupili tego samego dwa razy.

== Description ==

Registry dodaje listy prezentów do Twojego sklepu WooCommerce. Zalogowany klient tworzy nazwaną listę na wydarzenie (ślub, baby shower, urodziny, parapetówka lub inne), wybiera ze sklepu produkty, które chce, i otrzymuje link do udostępnienia znajomym i rodzinie.

Goście otwierają ten link, widzą, które pozycje są jeszcze potrzebne, i kupują prezent. Zakupione ilości są liczone na podstawie rzeczywistych zamówień WooCommerce, więc wszystko, co już zostało kupione, jest oznaczone jako w pełni zakupione i dwie osoby nie kupują tego samego dwa razy.

Kod źródłowy i zgłaszanie problemów na GitHubie: https://github.com/wppoland/registry

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/registry/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/registry/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/registry
* <strong>Zgłoszenia błędów i propozycje funkcji</strong> - https://github.com/wppoland/registry/issues
* <strong>Dyskusje i pytania</strong> - https://github.com/wppoland/registry/discussions


= Features =

* Klienci tworzą listy i zarządzają nimi w Moje konto → Listy prezentów.
* Każda lista ma typ wydarzenia (ślub, baby shower, urodziny, parapetówka, inne) i datę wydarzenia.
* Kontrolka «Dodaj do listy prezentów» na stronach pojedynczych produktów z żądaną ilością na pozycję.
* Publiczna strona listy tylko do odczytu z własnym permalinkiem, przeznaczona do udostępniania.
* Zakupione ilości są odczytywane z opłaconych zamówień WooCommerce, więc pozostałe liczniki pozostają aktualne bez ręcznych aktualizacji.
* Opcjonalny bezpośredni zakup ze współdzielonej strony; gdy jest wyłączony, goście trafiają na stronę produktu.
* Każde działanie sprawdza własność listy, więc jeden klient nie może edytować listy innego.

== Installation ==

1. Prześlij wtyczkę do `/wp-content/plugins/registry` lub zainstaluj przez Wtyczki → Dodaj nową.
2. Włącz ją. WooCommerce musi być zainstalowane i aktywne.
3. Listy są domyślnie włączone. Wejdź w WooCommerce → Listy prezentów, aby je wyłączyć lub wybrać, czy goście mogą kupować bezpośrednio ze współdzielonej strony.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Tak. WooCommerce musi być zainstalowane i aktywne.

= Who can create a registry? =

Każdy zalogowany klient, w obszarze Moje konto → Listy prezentów.

= How does purchase tracking work? =

Gdy prezent jest kupowany przez listę, lista, do której należy, jest zapisywana w pozycji zamówienia. Gdy zamówienie osiągnie status przetwarzany lub zrealizowany, ilość jest dodawana do licznika zakupów listy, a strona publiczna odejmuje ją od tego, co wciąż jest potrzebne. Każde zamówienie jest liczone tylko raz.

= Can guests buy directly from the shared page? =

Tak, jeśli w ustawieniach włączona jest opcja «Zezwól na zakup bezpośredni». W przeciwnym razie przycisk kupna odsyła gości na stronę produktu.

= Do guests need an account to view a shared registry? =

Nie. Publiczna strona listy jest tylko do odczytu dla każdego, kto ma link; tylko tworzenie i edycja list wymaga zalogowanego klienta.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest zgodna z WordPress Multisite. Włącz ją w całej sieci lub na poszczególnych witrynach; każda witryna zachowuje własne ustawienia i dane.

== Screenshots ==

1. W sklepie.
2. Ustawienia w kokpicie WordPress.
3. Na urządzeniu mobilnym.
== External Services ==

Registry nie łączy się z żadną usługą zewnętrzną. Nie wysyła wychodzących żądań sieciowych ani danych poza Twoją witrynę. Listy są przechowywane w WordPressie jako niestandardowy typ wpisu `gift_registry` z meta `_registry_*` (typ wydarzenia, data wydarzenia, wybrane pozycje i liczniki zakupów), a ustawienia wtyczki znajdują się w opcjach `registry_settings` i `registry_db_version`. Śledzenie zakupów odczytuje Twoje własne zamówienia WooCommerce i zapisuje `_registry_id`, `_registry_purchased` i `_registry_counted` w odpowiednich pozycjach zamówienia; wszystko pozostaje w Twojej bazie danych.

== Translations ==

Registry zawiera polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki. Domena tekstowa to `registry`, więc pakiety językowe z WordPress.org mogą również nadpisywać lub rozszerzać te dołączone tłumaczenia.

== Changelog ==

= 1.0.2 =
* Dodano dołączone polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki.

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.1.4 =
* Akcje `registry/purchase_recorded` i `registry/thankyou_purchase` po zliczeniu opłaconych prezentów z listy.
* Filtry `registry/theme`, `registry/theme_vars` i akcja `registry/public_hero` dla motywów list PRO.

= 0.1.3 =
* Filtry `registry/can_manage`, `registry/can_delete`, `registry/user_registries` i `registry/is_owner` dla współdzielonych list.
* Akcja `registry/account/single_registry`, filtr `registry/account/notices` i meta `_registry_contributors` dla list współwłaścicielskich.

= 0.1.2 =
* Filtr `registry/max_registries_limit`.
* Kontrole limitów list i powiadomienia w Moim koncie.

= 0.1.0 =
* Pierwsze wydanie.
