<?php

namespace App\Providers;

use App\Services\JsonLoader;
use Illuminate\Support\ServiceProvider;

class JsonConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        config(['settings' => JsonLoader::load()]);
    }
}
