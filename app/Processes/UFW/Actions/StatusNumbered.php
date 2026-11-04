<?php

namespace App\Processes\UFW\Actions;

class StatusNumbered extends UFWAction
{
    public function pipes(): array
    {
        return [
            'grep -E "^\["',
            'tr -s " "',
            'sed -e "s/[ \t]$//g"',
        ];
    }

}
