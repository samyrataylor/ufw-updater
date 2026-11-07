<?php

namespace App\Commands;


use App\Support\PathBuilder;

class AutoUpdate extends AbstractCronCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-update {--s|schedule= : Schedule to run on (default "0 1 * * *")}
                                        {--y|yes : Answer yes to any prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install cron job to run self-update';

    public function getScript(): string
    {
        return sprintf('%s/ufw-updater self-update', PathBuilder::path());
    }

    public function getDefaultSchedule(): string
    {
        return '0 1 * * *';
    }


}
