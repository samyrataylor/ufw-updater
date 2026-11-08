<?php

namespace App\Commands;

use App\Resources\Setup;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SetupFull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:full';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup and install cron jobs';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var Setup $setup */
        $setup = config('settings.setup');

        config(['cmd.setup.piped' => true]);
        $this->call('setup:ufw');

        if($setup->cron->run['install']) {
            $args = [
                '--yes' => true,
            ];

            if(!empty($setup->cron->run['schedule'])) {
                $args['--schedule'] = $setup->cron->run['schedule'];
            }

            $this->task('Adding rules:update cron job', fn() => $this->callSilently('install', $args));
        }

        if($setup->cron->update['install']) {
            $args = [
                '--yes' => true,
            ];

            if(!empty($setup->cron->update['schedule'])) {
                $args['--schedule'] = $setup->cron->update['schedule'];
            }

            $this->task('Adding self-update cron job', fn() => $this->callSilently('auto-update', $args));
        }

        $this->output->success('Setup complete');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
