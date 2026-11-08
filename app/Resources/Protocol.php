<?php

namespace App\Resources;

use App\Contracts\Resource;
use App\Enums\ProtocolType;


class Protocol implements Resource
{
    public const bool RECURSE = false;

    protected function __construct(
        protected(set) ProtocolType $protocol,
    ) {}

    public static function from(mixed $from = null): ?static
    {
        if($from instanceof self) {
            return $from;
        }

        if(!is_string($from)) {
            return self::Empty();
        }

        return match (strtolower($from)) {
            'udp'                    => self::UDP(),
            'tcp'                    => self::TCP(),
            'any' | 'anywhere' | '*' => self::Any(),
            default                  => self::Empty(),
        };
    }

    public static function UDP(): static
    {
        return new static(ProtocolType::UDP);
    }

    public static function TCP(): static
    {
        return new static(ProtocolType::TCP);
    }

    public static function Any(): static
    {
        return new static(ProtocolType::Any);
    }

    public static function Empty(): static
    {
        return new static(ProtocolType::Empty);
    }

    public function isTCP(): bool
    {
        return $this->protocol->isTCP();
    }

    public function isUDP(): bool
    {
        return $this->protocol->isUDP();
    }

    public function isEmpty(): bool
    {
        return $this->protocol->isEmpty();
    }

    public function is(string|ProtocolType $protocol): bool
    {
        return $this->protocol->is($protocol);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->protocol->value;
    }

    public function toJson($options = 0): string
    {
        return $this->toString();
    }

    public function toArray(): array
    {
        return ['protocol' => $this->protocol->value];
    }
}
