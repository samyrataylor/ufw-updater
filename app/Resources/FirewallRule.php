<?php

namespace App\Resources;

use App\Contracts\Resource;
use stdClass;

class FirewallRule implements Resource
{
    protected array $policies = [
        'ALLOW',
        'DENY',
        'DROP',
    ];

    protected(set) ?int $number = null;
    protected(set) int|string $port;
    protected(set) Protocol $protocol;
    protected(set) string $policy;
    protected(set) string $direction;
    protected(set) string $source;
    protected(set) bool $v6;
    protected(set) ?string $raw = null;
    protected(set) ?string $name = null;

    public function __construct(string|array $rule = [])
    {
        if (is_array($rule)) {
            foreach ($rule as $key => $value) {
                if(property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        if (is_string($rule)) {
            $this->raw = $rule;
            $this->name = static::parseName($rule);
            $this->parse($rule);
        }

        $this->setDefaults();
    }

    private function setDefaults(): void
    {
        foreach(static::getDefaults() as $key => $value) {
            if(!isset($this->$key)) {
                $this->$key = $value;
            }
        }
    }

    protected static function getDefaults(): array
    {
        return [
            'protocol' => Protocol::Empty(),
            'policy' => 'ALLOW',
            'direction' => 'IN',
            'source' => 'any',
            'v6' => false,
        ];
    }

    public function updateNumber(int $number): static
    {
        $this->number = $number;
        return $this;
    }

    public static function parseName(string $raw): string
    {
        return preg_replace('/^\[ *(\d)+] /', '', $raw);
    }

    public static function from(mixed $from = null): ?static
    {
        if($from === null) {
            return null;
        }

        if($from instanceof self) {
            return $from;
        }

        if($from instanceof stdClass) {
            return static::from(json_decode(json_encode($from), true));
        }

        if(is_int($from)) {
            return new static([
                'port' => $from,
                'protocol' => Protocol::Empty(),
                'policy' => 'ALLOW',
                'direction' => 'IN',
                'source' => 'any',
                'v6' => false,
            ]);
        }

        if(is_string($from)) {
            return new static()->parse($from);
        }

        if(is_array($from)) {
            try {
                return new static($from);
            } catch(\Throwable) {
                try {
                    return new static()->parseRawArray($from);
                } catch(\Throwable) {}
            }
        }

        return null;
    }

    private function parseRawArray(array $elements): static
    {
        foreach($elements as $element) {
            if(preg_match('/(\d+\/(tcp|udp))/', $element, $matchProtoPort)) {
                $this->port = (int) explode('/', $matchProtoPort[1])[0];
                $this->protocol = Protocol::from(explode('/', $matchProtoPort[1])[1]);
                continue;
            }

            $filter = filter_var($element, FILTER_VALIDATE_INT);

            if($filter !== false) {
                $this->port = (int) $filter;
                continue;
            }

            if(is_string($element)) {
                if(strtolower($element) === 'tcp' || strtolower($element) === 'udp') {
                    $this->protocol = Protocol::from(strtolower($element));
                    continue;
                }

                if(strtolower($element) === 'any' || strtolower($element) === 'anywhere') {
                    if(!isset($this->port)) {
                        $this->port = 'any';
                        $this->protocol = Protocol::Any();
                        continue;
                    }

                    if(!isset($this->protocol)) {
                        $this->protocol = Protocol::Any();
                        continue;
                    }

                    $this->source = 'any';
                    continue;
                }

                if(in_array(strtoupper($element), $this->policies)) {
                    $this->policy = strtoupper($element);
                    continue;
                }

                if(strtoupper($element) === 'IN' || strtoupper($element) === 'OUT') {
                    $this->direction = strtoupper($element);
                    continue;
                }

                $this->source = $element;
            }
        }

        return $this;
    }

    private function parse(string $rule): static
    {
        /*
         * UFW input
         * "[ 1] 22/tcp ALLOW IN 172.17.0.0/16 "
         * "[ 1] 22/tcp (v6) ALLOW IN Anywhere (v6) "
         * "[ 1] Anywhere ALLOW IN Anywhere "
         */

        if (str_contains($rule, '(v6)')) {
            $this->v6 = true;
            $rule = str_replace(' (v6)', '', $rule);
        } else {
            $this->v6 = false;
        }

        if(preg_match('/\[ *(\d*)] /', $rule, $matchNumber)) {
            $rule = str_replace($matchNumber[0], '', $rule);
            $this->number = (int) $matchNumber[1];
        }

       return $this->parseRawArray(
           explode(' ', trim($rule))
       );
    }

    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'protocol' => $this->protocol->toString(),
            'policy' => $this->policy,
            'direction' => $this->direction,
            'source' => $this->source,
            'v6' => $this->v6,
            'name' => $this->name,
            'raw' => $this->raw,
        ];
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toString(): string
    {
        return $this->toJson();
    }

    public function toFormattedString(): string
    {
        if($this->port === 'any') {
            return sprintf('any from %s', $this->source);
        }

        if($this->protocol === Protocol::Empty() || $this->protocol === Protocol::Any()) {
            return sprintf('%s from %s', $this->port, $this->source);
        }

        return sprintf('%s/%s from %s', $this->port, $this->protocol, $this->source);
    }
}
