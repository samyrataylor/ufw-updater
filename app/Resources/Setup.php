<?php

namespace App\Resources;

use App\Contracts\Resource;
use Illuminate\Support\Collection;

class Setup implements Resource
{
    protected(set) bool $enable;

    /**
     * @var Collection<array-key, FirewallRule> $rules
     */
    protected(set) Collection $rules;

    protected(set) CronJobs $cron;

    public function __construct(array $setup = [])
    {
        if(!empty($setup)){
            $this->rules = collect();
            $this->parse($setup);
        }
    }

    protected function parse(array $setup): void
    {
        if(array_key_exists('enable', $setup)) {
            $this->enable = filter_var($setup['enable'], FILTER_VALIDATE_BOOLEAN);
        }

        if(array_key_exists('rules', $setup) && !empty($setup['rules'])) {
            if(!is_array($setup['rules'])) {
                $setup['rules'] = [$setup['rules']];
            }

            foreach($setup['rules'] as $rule) {
                $rule = $this->parseRule($rule);
                if($rule instanceof FirewallRule) {
                    $this->rules->push($rule);
                }
            }
        }

        if(array_key_exists('cron', $setup) && !empty($setup['cron'])) {
            $cron = CronJobs::from($setup['cron']);
            if($cron instanceof CronJobs) {
                $this->cron = $cron;
            }
        }
    }

    protected function parseRule(array|string|int $rule): ?FirewallRule
    {
        return FirewallRule::from($rule);
    }

    public static function from(mixed $from = null): ?static
    {
        $from ??= [];
        return new static($from);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toArray(): array
    {
        return [
            'enable' => $this->enable,
            'rules' => $this->rules->map(
                fn(FirewallRule $rule) => array_filter($rule->toArray(), fn(mixed $item) => !empty($item))
            ),
            'cron' => $this->cron->toArray(),
        ];
    }


    public function toString(): string
    {
        return $this->toJson();
    }
}
