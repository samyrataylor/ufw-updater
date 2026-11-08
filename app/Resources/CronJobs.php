<?php

namespace App\Resources;

use App\Contracts\Resource;

class CronJobs implements Resource
{
    public const bool RECURSE = false;

    protected(set) array $run = [
        'install'  => true,
        'schedule' => '0 2 * * *',
    ];

    protected(set) array $update = [
        'install'  => true,
        'schedule' => '0 1 * * *',
    ];


    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if (array_key_exists($k, $this->$key)) {
                            $this->{$key}[$k] = $v;
                        }
                    }
                }
            }
        }
    }

    public static function from(mixed $from = null): ?static
    {
        if (is_array($from)) {
            return new static($from);
        }
        return null;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->toJson();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray(): array
    {
        return [
            'run' => $this->run,
            'update' => $this->update,
        ];
    }

}
