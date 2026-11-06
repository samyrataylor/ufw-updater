<?php

namespace App\Commands;

use App\Services\JsonLoader;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class GenerateConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:generate {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a fresh config.json';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $loader = new JsonLoader();

        if ($loader->exists()) {

            $overwrite = $this->option('force') ?: $this->confirm('Overwrite config.json?');
            if (!$overwrite) {
                $this->output->error('Cancelled');
                return;
            }
            $loader->delete();
        }

        if ($loader->create()) {
            $this->output->success('Config file generated successfully!');
            return;
        }

        $this->output->error('Could not create config file!');
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
