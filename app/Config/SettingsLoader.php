<?php

namespace App\Config;

use App\Contracts\ConfigLoader;

class SettingsLoader implements ConfigLoader
{
    public function load(): Repository
    {
        return new Repository(config('settings'));
    }


}
