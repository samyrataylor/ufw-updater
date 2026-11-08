<?php

use App\Resources\Protocol;
use App\Resources\Setup;

/*
 * Don't edit this file, values are here for IDE completion but are pulled from config.json
 */
return [
    'loaded' => false,

    'allowed_ips' => [],

    'domains' => [],

    'remove_others' => false,

    'remove_allow_any' => false,

    'protocol' => Protocol::Empty(),

    'port' => 22,

    'setup' => Setup::from([
        'enable' => false,
        'rules' => [],
        'cron' => [
            'run' => [
                'install' => false,
                'schedule' => '0 2 * * *',
            ],
            'update' => [
                'install' => false,
                'schedule' => '0 1 * * *',
            ]
        ],
    ]),
];
