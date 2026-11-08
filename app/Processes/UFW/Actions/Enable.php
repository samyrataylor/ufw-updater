<?php

namespace App\Processes\UFW\Actions;

class Enable extends UFWAction
{
    public function rawAction(): string
    {
        return '--force enable';
    }

}
