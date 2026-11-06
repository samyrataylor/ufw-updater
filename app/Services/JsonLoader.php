<?php

namespace App\Services;

use App\Support\JsonBuilder;
use App\Types\Protocol;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Throwable;

class JsonLoader
{
    protected(set) string $configFile = './config.json';

    protected array $fields = [
        'allowed_ips'      => 'array',
        'domains'          => 'array',
        'remove_others'    => 'boolean',
        'remove_allow_any' => 'boolean',
        'protocol'         => Protocol::class,
        'port'             => 'integer',
    ];

    public static function load(): array
    {
        return static::instance()->config();
    }

    public function exists(): bool
    {
        return file_exists($this->configFile);
    }

    public function delete(): bool
    {
        return unlink($this->configFile);
    }

    public function toJson(int $options = 0): string
    {
        return JsonBuilder::toJson($this->config(), $options);
    }

    public function config(): array
    {
        if (!$this->exists()) {
            return $this->defaultConfig();
        }

        $config = json_decode(file_get_contents($this->configFile), true);

        return $this->validatedItems($config);
    }

    protected function defaultConfig(): array
    {
        return [
            'allowed_ips'      => [],
            'domains'          => [],
            'remove_others'    => false,
            'remove_allow_any' => false,
            'protocol'         => Protocol::TCP(),
            'port'             => 22,
        ];
    }

    protected function validatedItems(array $items): array
    {
        $validated = [];

        foreach ($items as $key => $value) {
            $item = $this->validateField($key, $value);
            if ($item !== []) {
                $validated = array_merge($validated, $item);
            }
        }

        return $validated;
    }

    protected function validateField(string $field, mixed $value): array
    {
        if (!array_key_exists($field, $this->fields)) {
            return [];
        }

        if (gettype($value) === $this->fields[$field]) {
            return [$field => $value];
        }

        if ($this->fields[$field] === 'array') {
            if (is_string($value)) {
                return [$field => [$value]];
            }

            return [$field => []];
        }

        if ($this->fields[$field] === 'boolean') {
            return [$field => filter_var($value, FILTER_VALIDATE_BOOLEAN)];
        }

        if ($this->fields[$field] === 'integer') {
            return [$field => filter_var($value, FILTER_VALIDATE_INT)];
        }

        if ($this->fields[$field] === Protocol::class) {
            return [$field => Protocol::from($value)];
        }

        return [];
    }

    public static function instance(): static
    {
        return new static();
    }

    public static function generate(): bool
    {
        return static::instance()->create();
    }

    public function create(): bool
    {
        $config = Arr::mapWithKeys(
            $this->defaultConfig(),
            fn(mixed $value, string $key) => [
                $key => is_object($value) && method_exists($value, 'toJson') ? $value->toJson() : $value,
            ]
        );

        try {
            file_put_contents($this->configFile, json_encode($config, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
