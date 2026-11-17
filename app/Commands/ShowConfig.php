<?php

namespace App\Commands;

use App\Resources\Protocol;
use App\Resources\Setup;
use App\Services\JsonLoader;
use App\Support\JsonBuilder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
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
        $this->showJson();
    }

    protected function showJson(): void
    {
        $json = JsonBuilder::toJson(config('settings'), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        foreach(explode(PHP_EOL, $json) as $line) {
            $this->output->writeln($line);
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
