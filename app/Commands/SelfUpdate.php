<?php

namespace App\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use LaravelZero\Framework\Commands\Command;

class SelfUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'self-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Self update';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $data = null;

        $this->task('Grabbing latest release info', function () use (&$data)
        {
            $json = new Client()->get(config('repo.url'))->getBody()->getContents();
            $data = $this->toCollection(json_decode($json, true));
        });

        /** @var string $version */
        $version = $data->get('name');

        $this->output->writeln('<info>Latest release: '.$version.'</info>');

        if (config('app.version') === $version) {
            $this->output->info('Already up to date!');
            return;
        }

        $this->output->writeln('<comment>Current version: '.config('app.version').'</comment>');

        $downloadUrl = null;

        $this->task('Getting download url', function () use ($data, &$downloadUrl)
        {
            /** @var Collection $assets */
            $assets = $data->get('assets');
            /** @var Collection $appAsset */
            $appAsset = $assets->where('name', 'ufw-updater')->first();
            /** @var string $downloadUrl */
            $downloadUrl = $appAsset->get('browser_download_url');
        });

        $this->task('Downloading '.$version, fn() => exec(sprintf('curl -sL "%s" -o ufw-updater-%s', $downloadUrl, $version)));

        if (config('app.env') === 'production') {
            $this->task('Cleaning up old version', function () use ($version)
            {
                unlink('ufw-updater');
                rename('ufw-updater-'.$version, 'ufw-updater');
            });
        } else {
            $this->error('App not in production, could not auto update');
        }
    }

    protected function toCollection(iterable $data): Collection
    {
        foreach ($data as $key => $value) {
            if (is_iterable($value)) {
                $data[$key] = $this->toCollection($value);
            }
        }
        return collect($data);
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
