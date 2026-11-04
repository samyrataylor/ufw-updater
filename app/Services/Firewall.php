<?php

namespace App\Services;

use App\Processes\SystemProcess;
use App\Processes\UFW\Actions\Allow;
use App\Processes\UFW\Actions\Remove;
use App\Processes\UFW\Actions\StatusNumbered;
use App\Resources\FirewallRule;
use App\Types\Protocol;
use Illuminate\Support\Collection;

class Firewall
{
    /**
     * @var Collection<array-key, FirewallRule>
     */
    protected(set) Collection $rules;

    public function __construct()
    {
        $this->loadRules();
    }

    public static function parseRules(array|Collection $rules): Collection
    {
        return collect($rules)->mapInto(FirewallRule::class);
    }

    /**
     * @return Collection<array-key, FirewallRule>
     */
    public function rules(): Collection
    {
        return $this->rules->where('port', config('settings.port'))
                           ->where('protocol', config('settings.protocol'))
                           ->values();
    }

    private function processHook(SystemProcess $process): bool
    {
        $process->run();

        if($process->success) {
            try {
                $this->refreshRules();
            } catch (\Exception) {}
        }

        return $process->success;
    }

    public function allow(string $source, ?int $port = null, ?Protocol $protocol = null): bool
    {
        return $this->processHook(
            Allow::make($source, $port ?? config('settings.port'), $protocol ?? config('settings.protocol'))
        );
    }

    public function removeRule(FirewallRule $rule): bool
    {
        return $this->remove($rule->number);
    }

    public function remove(int|string $numOrSource): bool
    {
        if(is_int($numOrSource)) {
            return $this->processHook(
                Remove::make($numOrSource)
            );
        }

        $rule = $this->rules->where('source', $numOrSource)->first();

        if($rule) {
            return $this->remove($rule->number);
        }

        return false;
    }

    public function loadRules(): static
    {
        $this->rules = static::getRules();
        return $this;
    }

    public static function getRules(): Collection
    {
        $process = StatusNumbered::make();
        $output = $process->run();

        if($output === [] && $process->errorOutput() === []) {
            return static::parseRules([]);
        }

        if($process->failed) {
            throw new \Exception(implode(PHP_EOL, $process->errorOutput()));
        }

        return static::parseRules($output);
    }

    public function refreshRules(): static
    {
        $rules = static::getRules()->pluck('number', 'name')->toArray();

        foreach($this->rules as $key => $rule) {
            if(array_key_exists($rule->name, $rules)) {
                $this->rules[$key] = $rule->updateNumber($rules[$rule->name]);
            } else {
                unset($this->rules[$key]);
            }
        }

        $this->rules = $this->rules->values();

        return $this;
    }
}
