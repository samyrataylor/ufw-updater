<?php

namespace App\Config;

use App\Contracts\ConfigLoader;


class JsonLoader implements ConfigLoader
{
    public function load(): Repository
    {
        $file = config('settings.json_config_file', base_path('config.json'));

        if(file_exists($file)) {
            return new Repository(json_decode(file_get_contents($file), true));
        }

        return new Repository();
    }


}
