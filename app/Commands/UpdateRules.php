<?php

namespace App\Commands;

use App\Processes\Dig;
use App\Services\Firewall;
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
    protected $description = 'Command description';
    protected Firewall $firewall;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->task('Loading firewall', fn() => $this->firewall = new Firewall());
        $this->output->writeln('Found '.count($this->firewall->rules()).' firewall rule(s)');

        if(count($this->firewall->rules())) {
            $this->task(
                'Removing existing rules',
                fn() => $this->removeRules());
        }

        $allowed = config('settings.allowed_ips');
        $domains = config('settings.domains');

        foreach($allowed as $ip) {
            $this->task(
                'Adding allowed IP '.$ip,
                fn() => $this->firewall->allow($ip)
            );
        }

        foreach($domains as $domain) {
            $this->task(
                'Adding IP for '.$domain,
                fn() => $this->firewall->allow(Dig::make($domain)->run())
            );

        }

    }

    private function removeRules(): void
    {
        foreach($this->firewall->rules() as $rule) {
            $this->firewall->removeRule($rule);
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
