<?php

namespace App\Commands;

use App\Processes\Dig;
use App\Resources\FirewallRule;
use App\Services\Firewall;
use App\Tasks\LoadFirewall;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class UpdateRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rules:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update UFW rules based on config.json';
    protected(set) Firewall $firewall;
    protected(set) array $allowed = [];
    protected(set) array $domains = [];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->task('Loading firewall', fn() => $this->firewall = new Firewall());
        $this->output->writeln('Found '.count($this->firewall->rules()).' firewall rule(s)');

        if (config('settings.remove_allow_any')) {
            $this->removeAllowAny();
        }

        if (config('settings.remove_others')) {
            $this->removeOthers();
        }

        $this->handleAllowList();
        $this->handleDomains();

        $this->output->success('Completed');
    }

    private function handleAllowList(): void
    {
        $this->task('Loading allowed IPs', fn() => $this->allowed = config('settings.allowed_ips'));
        $this->output->writeln('Found '.count($this->allowed).' allowed IP(s)');

        foreach ($this->allowed as $ip) {
            $this->task('Adding IP '.$ip, fn() => $this->firewall->allow($ip));
        }
    }

    private function handleDomains(): void
    {
        $this->task('Loading domains', fn() => $this->domains = config('settings.domains'));
        $this->output->writeln('Found '.count($this->domains).' domain(s)');

        foreach ($this->domains as $domain) {
            $ip = null;

            $this->task('Getting IP from '.$domain, function () use (&$ip, $domain)
            {
                $ip = Dig::make($domain)->run();
            });

            if (!$ip) {
                $this->output->writeln('<error>'.$domain.' failed to resolve.</error>');
                continue;
            }
            $this->output->writeln('<info>'.$domain.' => '.$ip.'</info>');
            $this->task('Adding IP '.$ip, fn() => $this->firewall->allow($ip));
        }
    }

    private function removeRule(FirewallRule $rule): void
    {
        $this->task('Removing rule '.$rule->number, fn() => $this->firewall->removeRule($rule));
    }

    private function removeOthers(): void
    {
        $this->output->writeln('Found '.count($this->firewall->rules()).' other rule(s)');
        foreach ($this->firewall->rules() as $rule) {
            $this->task('Removing rule '.$rule->number, fn() => $this->firewall->removeRule($rule));
        }
    }

    private function removeAllowAny(): void
    {
        $rules = null;
        $firewall = $this->firewall;
        $this->task('Finding rules with source "any"', function () use (&$rules, $firewall)
        {
            $rules = $firewall->rules()->where('source', 'any')->values();
        });
        $this->output->writeln('Found '.count($rules).' rule(s) from source "any"');

        foreach ($rules as $rule) {
            $this->removeRule($rule);
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
