<?php

namespace App\Services;

use App\Config\Config;
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

    protected(set) Config $config;

    public function __construct()
    {
        $this->config = Config::make();
    }

    public function parseRules(array|Collection $rules): static
    {
        $this->rules = collect($rules)->mapInto(FirewallRule::class);
        return $this;
    }

    public function rules(): Collection
    {
        return $this->rules;
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
            Allow::make($source, $port ?? $this->config->get('port'), $protocol ?? $this->config->get('protocol'))
        );
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

    public function refreshRules(): static
    {
        $process = StatusNumbered::make();
        $output = $process->run();

        if($process->failed) {
            throw new \Exception(implode(PHP_EOL, $process->errorOutput()));
        }

        $this->parseRules($output);

        return $this;
    }
}
