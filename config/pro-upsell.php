<?php
/**
 * PRO upsell content, generated from the plogins.com registry by
 * scripts/gen-pro-upsell.mjs. The admin upsell renders this; curate the
 * feature list to fit this plugin's settings screen (do not invent features).
 *
 * @package registry-pro
 */

defined('ABSPATH') || exit;

return [
    'name'       => 'Registry Pro',
    'url'        => 'https://plogins.com/registry-pro/pricing/',
    'sellable'   => true,
    'price_from' => 29,
    'currency'   => 'EUR',
    'price_pln'  => 129,
    'lead'       => [
        'en' => 'The 0.4.0 release completes the Track 3 roadmap with registry themes and thank-you tracking.',
        'pl' => 'Wydanie 0.4.0 uzupełnia roadmapę Track 3 o motywy list i śledzenie podziękowań.',
    ],
    'features'   => [
        [
            'en' => ['title' => 'Owner notifications', 'desc' => 'Email the registry owner when a guest buys a gift from their list (shipped).'],
            'pl' => ['title' => 'Powiadomienia właściciela', 'desc' => 'E-mail do właściciela listy po zakupie prezentu przez gościa (wdrożone).'],
        ],
        [
            'en' => ['title' => 'Multiple registries per customer', 'desc' => 'One customer runs several registries side by side (shipped).'],
            'pl' => ['title' => 'Wiele list na klienta', 'desc' => 'Jeden klient prowadzi kilka list obok siebie (wdrożone).'],
        ],
        [
            'en' => ['title' => 'Group gifting and contributions', 'desc' => 'Co-owners and group contributions (GroupGifting, shipped).'],
            'pl' => ['title' => 'Prezenty grupowe i dopłaty', 'desc' => 'Współwłaściciele i składki na prezenty (GroupGifting, wdrożone).'],
        ],
        [
            'en' => ['title' => 'Registry themes', 'desc' => 'Branded public pages with presets, cover image and welcome message (RegistryThemes, shipped).'],
            'pl' => ['title' => 'Motywy listy', 'desc' => 'Markowe strony publiczne z presetami, okładką i komunikatem (RegistryThemes, wdrożone).'],
        ],
        [
            'en' => ['title' => 'Thank-you tracking', 'desc' => 'Log gift givers and mark thank-you notes as sent (ThankYouTracking, shipped).'],
            'pl' => ['title' => 'Śledzenie podziękowań', 'desc' => 'Log kto kupił prezent i oznaczanie wysłanych podziękowań (ThankYouTracking, wdrożone).'],
        ],
    ],
];
