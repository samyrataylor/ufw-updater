<?php

namespace App\Commands;

use App\Config\Config;
use App\Processes\Dig;
use App\Processes\UFW\Actions\Allow;
use App\Processes\UFW\Actions\StatusNumbered;
use App\Resources\FirewallRule;
use App\Resources\FirewallRuleCollection;
use App\Types\Protocol;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Process;
use LaravelZero\Framework\Commands\Command;


class ListRules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rules:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $x = collect([
            "[ 1] 22/tcp ALLOW IN 172.17.0.0/16 ",
            "[ 2] 22/tcp ALLOW IN Anywhere ",
            "[ 3] 80/tcp ALLOW IN Anywhere ",
            "[ 4] 22/tcp (v6) ALLOW IN Anywhere (v6) ",
            "[ 5] 80/tcp (v6) ALLOW IN Anywhere (v6)",
        ])->mapInto(FirewallRule::class);

        dd($x->first()->toAllowProcess());
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
