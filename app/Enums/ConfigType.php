<?php

namespace App\Enums;

use App\Config\JsonLoader;
use App\Config\SettingsLoader;
use App\Contracts\ConfigLoader;

enum ConfigType: string
{
    case Settings = 'settings';
    case Json = 'json';

    public function getLoader(): ConfigLoader
    {
        return match ($this) {
            self::Settings => new SettingsLoader(),
            self::Json => new JsonLoader(),
        };
    }
}
