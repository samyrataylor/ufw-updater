<?php

namespace App\Commands;

use App\Services\JsonLoader;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ShowConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:show';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current config';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $config = explode(PHP_EOL, JsonLoader::instance()->toJson(JSON_PRETTY_PRINT));
        $this->output->writeln($config);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
