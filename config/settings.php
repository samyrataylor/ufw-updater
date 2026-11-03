<?php

use App\Types\Protocol;

return [
    'use_json_config' => env('USE_JSON_CONFIG', true),

    'overwrite_json' => env('OVERWRITE_JSON_CONFIG', true),

    'json_config_file' => env('JSON_CONFIG_FILE', base_path('config.json')),

    'allowed_ips' => [],

    'blocked_ips' => [],

    'domains' => [],

    'remove_others' => env('REMOVE_OTHERS', true),

    'remove_allow_any' => env('REMOVE_ALLOW_ANY', true),

    'protocol' => Protocol::from(env('PROTOCOL', 'tcp')),

    'port' => env('PORT', 22),
];
