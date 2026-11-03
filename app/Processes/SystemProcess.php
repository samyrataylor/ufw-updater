<?php

namespace App\Processes;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;

abstract class SystemProcess
{
    protected(set) bool $hasExecuted = false;
    protected(set) bool $failed;
    protected(set) bool $success;
    protected(set) int $exitCode;
    protected(set) ProcessResult $processResult;

    public static function elevated(): bool
    {
        return false;
    }

    abstract public static function rawCommand(): string;

    public function placeholders(): array
    {
        return [];
    }

    public function command(): string
    {
        $command = $this->rawCommand();

        if($this->elevated()) {
            $command = 'sudo '.$command;
        }

        return strtr(
            $command,
            Arr::mapWithKeys(
                $this->placeholders(),
                fn(string $value, string $key) => ['{'.$key.'}' => $value]
            )
        );
    }

    public function pipes(): array
    {
        return [];
    }

    public function getCommandWithPipes(): array
    {
        return array_merge([$this->command()], $this->pipes());
    }

    public function execute(): ProcessResult
    {
        return $this->executeWithPipes();
    }

    private function updateStatus(): void
    {
        if(isset($this->processResult)) {
            $this->hasExecuted = true;
            $this->exitCode = $this->exitCode();
            $this->failed = $this->exitCode !== 0;
            $this->success = $this->exitCode === 0;
        }
    }

    public function executeWithPipes(array $pipes = []): ProcessResult
    {
        $this->processResult = Process::pipe(array_merge($this->getCommandWithPipes(), $pipes));
        $this->updateStatus();
        return $this->processResult;
    }

    public function executeWithoutPipes(): ProcessResult
    {
        $this->processResult = Process::run($this->command());
        $this->updateStatus();
        return $this->processResult;
    }

    public function run(): mixed
    {
        $this->execute();
        return $this->toOutput();
    }

    public function runWithoutPipes(): mixed
    {
        $this->executeWithoutPipes();
        return $this->toOutput();
    }

    public function runWithPipes(array $pipes = []): mixed
    {
        $this->executeWithPipes($pipes);
        return $this->toOutput();
    }

    public function exitCode(): int
    {
        return $this->getOutputData()['exit_code'];
    }

    public function errorOutput(): array
    {
        return $this->getOutputData()['error'];
    }

    private function toOutput(): mixed
    {
        $output = $this->getOutputData();
        return $this->formatOutput($output['output'], $output['error']);
    }

    private function getOutputData(): array
    {
        return [
            'output' => $this->filterOutput($this->processResult->output()),
            'error' => $this->filterOutput($this->processResult->errorOutput()),
            'exit_code' => $this->processResult->exitCode(),
        ];
    }

    private function filterOutput(array|string $output): array
    {
        if(is_string($output)) {
            $output = explode(PHP_EOL, $output);
        }

        return array_filter($output, fn(string $line) => !empty($line));
    }

    protected function formatOutput(array $output, array $error): mixed
    {
        return $output;
    }

    public static function make(...$args): static
    {
        return new static(...$args);
    }
}
