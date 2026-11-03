<?php

namespace App\Processes\UFW;

use App\Processes\SystemProcess;
use Illuminate\Support\Arr;

abstract class UFW extends SystemProcess
{
    public static function elevated(): bool
    {
        return true;
    }

    public function placeholders(): array
    {
        return [
            'action' => $this->action(),
        ];
    }

    public static function rawCommand(): string
    {
        return 'ufw {action}';
    }

    abstract public function rawAction(): string;

    abstract public function action(): string;
}
