<?php

require_once 'constants.inc.php';

$game_preferences = [
    OPTION_CONFIRM => [
        'name' => totranslate('Turn confirmation'),
        'needReload' => false,
        'values' => [
            OPTION_CONFIRM_TIMER => [
                'name' => totranslate('Enabled with timer'),
            ],
            OPTION_CONFIRM_ENABLED => ['name' => totranslate('Enabled')],
            OPTION_CONFIRM_DISABLED => ['name' => totranslate('Disabled')],
        ],
    ],
];
