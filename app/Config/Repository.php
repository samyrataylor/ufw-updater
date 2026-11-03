<?php

namespace App\Config;

use App\Contracts\ConfigRepository;
use App\Types\Protocol;
use Illuminate\Config\Repository as IlluminateRepository;

/**
 * @mixin IlluminateRepository
 */
class Repository extends IlluminateRepository implements ConfigRepository
{
    protected array $fields = [
        'allowed_ips' => 'array',
        'blocked_ips' => 'array',
        'domains' => 'array',
        'remove_others' => 'boolean',
        'remove_allow_any' => 'boolean',
        'protocol' => Protocol::class,
        'port' => 'integer',
    ];

    public function __construct(?array $items = [])
    {
        if($items === null) {
            $items = [];
        }

        parent::__construct($this->validatedItems($items));
    }

    protected function validatedItems(array $items): array
    {
        $validated = [];

        foreach($items as $key => $value) {
            $item = $this->validateField($key, $value);
            if($item !== []) {
                $validated = array_merge($validated, $item);
            }
        }

        return $validated;
    }

    protected function validateField(string $field, mixed $value): array
    {
        if(!array_key_exists($field, $this->fields)) {
            return [];
        }

        if(gettype($value) === $this->fields[$field]) {
            return [$field => $value];
        }

        if($this->fields[$field] === 'array') {
            if(is_string($value)) {
                return [$field => [$value]];
            }

            return [$field => []];
        }

        if($this->fields[$field] === 'boolean') {
            return [$field => filter_var($value, FILTER_VALIDATE_BOOLEAN)];
        }

        if($this->fields[$field] === 'integer') {
            return [$field => filter_var($value, FILTER_VALIDATE_INT)];
        }

        if($this->fields[$field] === Protocol::class) {
            return [$field => Protocol::from($value)];
        }

        return [];
    }
}
