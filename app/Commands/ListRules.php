<?php

namespace App\Commands;

use App\Resources\FirewallRule;
use App\Services\Firewall;
use App\Support\JsonBuilder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;
use function Termwind\parse;

class ListRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rules:list
                            {--a|all : List all rules}
                            {--u|ufw : Display rule as it appears in UFW}
                            {--r|raw : Raw output (no table)}
                            {--j|json : JSON output (implies --raw)}
                            {--c|compact : Compact output (ignored without --json)}
                            {--f|flat : Flatten JSON output (ignored without --json, implies --no-numbers and --ufw)}
                            {--b|no-numbers : Don\'t include rule numbers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List UFW rules';

    protected(set) Firewall $firewall;

    /**
     * @var Collection<array-key, FirewallRule>
     */
    protected(set) Collection $rules;

    protected(set) bool $raw;
    protected(set) bool $numbers;

    protected(set) bool $ufw;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->firewall = new Firewall();
        $this->rules = $this->option('all') ? $this->firewall->rules : $this->firewall->rules();

        $this->raw = $this->option('raw');
        $this->numbers = !$this->option('no-numbers');
        $this->ufw = $this->option('ufw');

        if($this->option('json')) {
            $this->raw = true;
            $this->displayJSONOutput();
            return;
        }

        if($this->ufw) {
            $this->displayUFWOutput();
            return;
        }

        $this->displayFormattedRules();
    }

    public function checkMark(): string|bool
    {
        return $this->raw ? true : parse('<strong><span class="text-green">✔</span></strong>');
    }

    public function xMark(): string|bool
    {
        return $this->raw ? false : parse('<strong><span class="text-rose-600">✘</span></strong>');
    }

    /**
     * @return Collection<array-key, array>
     */
    protected function getMappedRules(): Collection
    {
        if(!$this->numbers) {
            $map = fn (FirewallRule $rule) => [
                $rule->port,
                $rule->protocol,
                $rule->policy,
                $rule->direction,
                $rule->source,
                $rule->v6 ? $this->checkMark() : $this->xMark()
            ];
        } else {
            $map = fn (FirewallRule $rule) => [
                $rule->number,
                $rule->port,
                $rule->protocol,
                $rule->policy,
                $rule->direction,
                $rule->source,
                $rule->v6 ? $this->checkMark() : $this->xMark()
            ];
        }

        return $this->rules->map($map);
    }

    /**
     * @return Collection<array-key, array>
     */
    protected function getRules(): Collection
    {
        if(!$this->numbers) {
            $map = fn (FirewallRule $rule) => [
                $rule->name,
            ];
        } else {
            $map = fn (FirewallRule $rule) => [
                $rule->number,
                $rule->name,
            ];
        }

        if($this->ufw) {
            return $this->rules->map($map);
        }

        return $this->getMappedRules();
    }

    protected function getInitialHeaders(): array
    {
        return !$this->numbers ? [] : ['Number'];
    }

    protected function getHeaders(bool $lowercase = false): array
    {
        $callback = fn(string $item) => $lowercase ? strtolower($item) : $item;

        if($this->ufw) {
            $headers = array_merge($this->getInitialHeaders(), ['Rule']);
        } else {
            $headers = array_merge($this->getInitialHeaders(), [
                'Port',
                'Protocol',
                'Policy',
                'Direction',
                'Source',
                'v6',
            ]);
        }

        return array_map($callback, $headers);
    }

    protected function displayFormattedRules(): void
    {
        if($this->raw) {
            foreach($this->getRules() as $rule) {
                $rule = array_map(fn($item) => is_bool($item) ? ($item ? '(v6)' : '') : $item, $rule);
                $this->output->writeln(implode(' ', $rule));
            }
        } else {
            $this->table($this->getHeaders(), $this->getRules());
        }
    }

    protected function displayUFWOutput(): void
    {
        if($this->raw) {
            foreach($this->getRules() as $rule) {
                $this->output->writeln(implode(' ', $rule));
            }
        } else {
            $this->table($this->getHeaders(), $this->getRules());
        }
    }

    protected function displayJsonOutput(): void
    {
        if($this->option('flat')) {
            $this->ufw = true;
            $this->numbers = false;
            $rules = $this->getRules()->flatten();
        } else {
            $rules = $this->getRules()->map(fn(array $rule) => array_combine($this->getHeaders(true), $rule));
        }

        if($this->option('compact')) {
            $this->output->writeln(JsonBuilder::toJson($rules, JSON_UNESCAPED_SLASHES));
            return;
        }
        $this->output->writeln(JsonBuilder::toPrettyJson($rules, JSON_UNESCAPED_SLASHES));
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
