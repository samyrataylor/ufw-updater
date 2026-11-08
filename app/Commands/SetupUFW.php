<?php

namespace App\Commands;

use App\Resources\FirewallRule;
use App\Resources\Setup as SetupResource;
use App\Services\Firewall;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SetupUFW extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:ufw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup UFW with config defaults';

    protected(set) Firewall $firewall;
    protected(set) SetupResource $setup;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->task('Loading firewall service', fn() => $this->firewall = new Firewall());
        $this->task('Loading setup from config.json', fn() => $this->setup = config('settings.setup'));

        if($this->setup->enable) {
            $this->task('Enabling firewall', fn() => $this->firewall->enable());
        }

        $this->output->writeln(sprintf('Found %s rule(s) to add', count($this->setup->rules)));

        /** @var FirewallRule $rule */
        foreach($this->setup->rules as $rule) {
            if(!$this->firewall->isSupported($rule)) {
                $this->output->writeln(
                    sprintf(
                        '<comment>Skipping unsupported rule %s</comment>',
                        $rule->toFormattedString()
                    )
                );
                continue;
            }

            $this->task(
                sprintf(
                    'Adding rule %s',
                    $rule->toFormattedString()
                ),
                fn() => $this->firewall->addRule($rule)
            );
        }

        if(!config('cmd.setup.piped', false)) {
            $this->output->success('Setup complete');
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
