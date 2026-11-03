<?php

namespace App\Config;

use App\Enums\ConfigType;
use Throwable;

/**
 * @mixin Repository
 */
class Config
{
    protected(set) ConfigType $type;
    protected(set) Repository $config;

    public function __construct()
    {
        if(config('settings.use_json_config', false)) {
            $this->type = ConfigType::Json;
        } else {
            $this->type = ConfigType::Settings;
        }

        $this->config = $this->type->getLoader()->load();
    }

    public static function make(): static
    {
        return new static();
    }

    public static function __callStatic(string $name, array $arguments): mixed
    {
        return static::make()->{$name}(...$arguments);
    }

    public function __call(string $name, array $arguments): mixed
    {
        try {
            return $this->config->{$name}(...$arguments);
        } catch (Throwable) {
            return null;
        }
    }
}
