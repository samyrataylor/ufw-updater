<?php

namespace App\Resources;

use App\Types\Protocol;

class FirewallRule
{
    protected(set) int $number;
    protected(set) int|string $port;
    protected(set) Protocol $protocol;
    protected(set) string $policy;
    protected(set) string $direction;
    protected(set) string $source;
    protected(set) bool $v6;
    protected(set) ?string $raw = null;
    protected(set) ?string $name = null;

    public function __construct(string|array $rule)
    {
        if (is_array($rule)) {
            foreach ($rule as $key => $value) {
                $this->$key = $value;
            }
        }

        if (is_string($rule)) {
            $this->raw = $rule;
            $this->name = static::parseName($rule);
            $this->parse($rule);
        }
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

    private function parse(string $rule): void
    {
        /*
         * Expected input
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

        preg_match('/\[ *(\d*)]/', $rule, $matchNumber);
        $rule = str_replace($matchNumber[0], '', $rule);
        $this->number = (int) $matchNumber[1];

        preg_match('/ ((\d+\/(tcp|udp))|Anywhere) /', $rule, $matchProtoPort);

        if($matchProtoPort[1] === 'Anywhere') {
            $rule = implode(' ', array_slice(explode(' ', trim($rule)), 1));
            $this->port = 'any';
            $this->protocol = Protocol::Any();
        } else {
            $rule = str_replace($matchProtoPort[0], '', $rule);
            $this->port = (int) explode('/', $matchProtoPort[1])[0];
            $this->protocol = Protocol::from(explode('/', $matchProtoPort[1])[1]);
        }

        $rule = trim($rule);

        $exploded = explode(' ', $rule);
        $this->policy = $exploded[0];
        $this->direction = $exploded[1];
        $this->source = $exploded[2];

        if ($this->source === 'Anywhere') {
            $this->source = 'any';
        }
    }
}
