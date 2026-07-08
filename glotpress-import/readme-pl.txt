=== Registry - Gift Registry for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, gift registry, wishlist, wedding, baby shower
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Wymaga wtyczek: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Pozwól klientom tworzyć rejestry prezentów, które można udostępniać na wesela, baby shower i imprezy, ze śledzeniem zakupów, aby goście nigdy nie robili podwójnych zakupów.

== Description ==

Rejestr dodaje rejestry prezentów do Twojego sklepu WooCommerce. Zalogowany klient tworzy rejestrację nazwaną dla wydarzenia (ślub, baby shower, urodziny, parapetówka lub inne), wybiera ze sklepu produkty, które chce, i otrzymuje link, który można udostępnić, aby wysłać go znajomym i rodzinie.

Goście otwierają ten link, sprawdzają, które przedmioty są jeszcze potrzebne i kupują prezent. Zakupione ilości są liczone na podstawie rzeczywistych zamówień WooCommerce, więc wszystko, co już zostało zakupione, jest oznaczane jako w pełni zakupione, a dwie osoby nie kupują tego samego dwa razy.

Narzędzie do śledzenia źródeł i problemów dostępne na GitHubie: https://github.com/wppoland/registry

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/registry/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/registry/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/registry
* <strong>Raporty o błędach i prośby o nowe funkcje</strong> - https://github.com/wppoland/registry/issues
* <strong>Dyskusje i pytania</strong> - https://github.com/wppoland/registry/discussions


= Features =

* Klienci tworzą swoje rejestry i zarządzają nimi w obszarze Moje konto → Rejestry prezentów.
* Każdy rejestr posiada typ wydarzenia (ślub, baby shower, urodziny, parapetówkę, inne) oraz datę wydarzenia.
* Opcja „Dodaj do listy prezentów” na stronach pojedynczych produktów, z żądaną ilością przypadającą na sztukę.
* Publiczna strona rejestru przeznaczona tylko do odczytu, z własnym łączem bezpośrednim, przeznaczona do udostępniania.
* Zakupione ilości są odczytywane z opłaconych zamówień WooCommerce, więc pozostałe liczniki pozostają aktualne bez ręcznych aktualizacji.
* Opcjonalny bezpośredni zakup z udostępnionej strony; gdy jest wyłączona, goście będą zamiast tego odsyłani do strony produktu.
* Każde działanie sprawdza własność rejestru, więc jeden klient nie może edytować rejestru innego.

== Installation ==

1. Prześlij wtyczkę do `/wp-content/plugins/registry` lub zainstaluj poprzez Wtyczki → Dodaj nową.
2. Aktywuj. WooCommerce musi być zainstalowany i aktywny.
3. Rejestry są domyślnie włączone. Odwiedź WooCommerce → Rejestry prezentów, aby je wyłączyć lub wybrać, czy goście mogą kupować bezpośrednio z udostępnionej strony.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Tak. WooCommerce musi być zainstalowany i aktywny.

= Who can create a registry? =

Dowolny zalogowany klient z obszaru Moje konto → Rejestry prezentów.

= How does purchase tracking work? =

W przypadku zakupu prezentu za pośrednictwem rejestru rejestr, do którego należy, jest zapisywany w pozycji zamówienia. Kiedy zamówienie zostanie przetworzone lub ukończone, ilość zostanie dodana do liczby zakupionych towarów w rejestrze, a strona publiczna odejmie ją od jeszcze potrzebnej ilości. Każde zamówienie liczone jest tylko raz.

= Can guests buy directly from the shared page? =

Tak, jeśli w ustawieniach włączona jest opcja „Zezwól na zakup bezpośredni”. W przeciwnym razie przycisk Kup odsyła gości do strony produktu.

= Do guests need an account to view a shared registry? =

Nie. Strona rejestru publicznego jest przeznaczona tylko do odczytu dla każdej osoby mającej łącze. jedynie tworzenie i edytowanie rejestrów wymaga zalogowanego klienta.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest kompatybilna z WordPress Multisite. Aktywuj go w sieci lub aktywuj na poszczególnych stronach; każda witryna przechowuje własne ustawienia i dane.

== Screenshots ==

1. Na wystawie sklepowej.
2. Ustawienia w panelu administracyjnym WordPress.
3. Na urządzeniu mobilnym.
== External Services ==

Rejestr nie łączy się z żadną usługą zewnętrzną. Nie wysyła żadnych wychodzących żądań sieciowych i nie wysyła żadnych danych poza Twoją witrynę. Rejestry są przechowywane w WordPressie jako niestandardowy typ postu `gift_registry` z meta postem `_registry_*` (typ zdarzenia, data wydarzenia, wybrane elementy i liczba zakupionych rzeczy), a ustawienia wtyczki są dostępne w opcjach `registry_settings` i `registry_db_version`. Śledzenie zakupów odczytuje Twoje własne zamówienia WooCommerce i rejestruje `_registry_id`, `_registry_purchased` i `_registry_counted` w odpowiednich pozycjach zamówienia; wszystko pozostaje w Twojej bazie danych.

== Changelog ==

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.1.4 =
* Akcje `rejestracja/zakup_zakupu` i `rejestracja/podziękowanie_zakupu` po zliczeniu opłaconych prezentów rejestracyjnych.
* Filtry `rejestr/motyw`, `rejestr/zmienne_motywu` i akcja `rejestr/public_hero` dla motywów rejestru PRO.

= 0.1.3 =
* Dodaj filtry rejestru/can_manage, rejestru/can_delete, rejestru/rejestru_użytkownika i rejestru/jest_właścicielem dla rejestrów współdzielonych.
* Dodaj akcję rejestru/konta/pojedynczego_rejestru, filtr rejestru/konta/powiadomień i meta pomocnika _registry_contributors dla list współwłaścicieli.

= 0.1.2 =
* Dodaj filtr rejestru/max_registries_limit.
* Dodaj kontrole limitów rejestru i powiadomienia na Moim koncie.

= 0.1.0 =
* Pierwsze wydanie.
