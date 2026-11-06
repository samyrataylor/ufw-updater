<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

abstract class AbstractCronCommand extends Command
{
    protected(set) string $schedule;

    protected(set) string $script;

    protected(set) bool $exists;

    abstract public function getScript(): string;

    abstract public function getDefaultSchedule(): string;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->schedule = $this->option('schedule') ?? $this->getDefaultSchedule();

        if(!$this->validateSchedule()) {
            $this->output->error($this->schedule . ' is not a valid schedule. Use cron syntax and surround in quotes. Ex: --schedule="0 2 * * *"');
            return;
        }

        $this->script = $this->getScript();

        $this->prepare()->installJob();
    }

    protected function installJob(): void
    {
        if($this->exists) {
            return;
        }

        $command = sprintf('(crontab -l 2>/dev/null; echo "%s %s") | crontab -', $this->schedule, $this->script);

        $this->task('Installing cron job', fn () => shell_exec($command));

        $this->output->success('Cron job installed');
    }

    protected function validateSchedule(): bool
    {
        return count(explode(' ', $this->schedule)) === 5;
    }

    protected function prepare(): static
    {
        $output = shell_exec(sprintf('crontab -l | grep "%s"', $this->script));

        if($output === null) {
            $this->exists = false;
            return $this;
        }

        $current = implode(' ', array_slice(explode(' ', $output), 0, 5));

        if($current === $this->schedule) {
            $this->output->info('Cron job already installed.');
            $this->exists = true;
            return $this;
        }

        return $this->handleExisting($current);
    }

    protected function handleExisting(string $current): static
    {
        $this->output->writeln('<comment>Cron job already installed with different schedule: [' . $current . ']</comment>');

        $confirm = $this->option('yes') || $this->confirm('Update cron job with new schedule?');

        if(!$confirm) {
            $this->exists = true;
            $this->output->writeln('<comment>Cron tab unchanged.</comment>');
            return $this;
        }

        return $this->removeExisting($current);
    }

    protected function removeExisting(string $current): static
    {
        $this->task('Removing existing cron job', function () use ($current) {
            $updated = array_filter(
                explode(PHP_EOL, shell_exec('crontab -l')),
                fn(string $line) => !empty($line) && $line !== implode(' ', [$current, $this->script])
            );

            shell_exec(sprintf('echo "%s" | crontab -', implode(PHP_EOL, $updated)));
        });

        $this->exists = false;

        return $this;
    }
}
