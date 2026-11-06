<?php

namespace App\Enums;

use Throwable;

enum ProtocolType: string
{
    case TCP = 'tcp';
    case UDP = 'udp';
    case Empty = 'empty';
    case Any = 'any';

    public function is(string|self|null $type): bool
    {
        if ($type instanceof self) {
            return $this === $type;
        }

        $type = strtolower($type);

        if (empty($type) || $type === 'empty') {
            $type = 'empty';
        }


        if($type === 'anywhere' || $type === '*') {
            $type = 'any';
        }

        try {
            return $this === ProtocolType::from($type);
        } catch (Throwable) {
            return false;
        }
    }

    public function isEmpty(): bool
    {
        return $this->is(self::Empty);
    }

    public function isAny(): bool
    {
        return $this->is(self::Any);
    }

    public function isTCP(): bool
    {
        return $this->is(self::TCP);
    }

    public function isUDP(): bool
    {
        return $this->is(self::UDP);
    }
}
