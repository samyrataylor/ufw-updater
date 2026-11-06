<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;

class InstallJob extends AbstractCronCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install {--s|schedule= : Schedule to run on (default "0 2 * * *")}
                                    {--y|yes : Answer yes to any prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install cron job to run rules:update';

    public function getScript(): string
    {
        return sprintf('%s/ufw-updater rules:update', getcwd());
    }

    public function getDefaultSchedule(): string
    {
        return '0 2 * * *';
    }
}
