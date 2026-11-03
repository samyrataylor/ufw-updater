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
            'grep -E "22/tcp ALLOW IN"'
        ];
    }

    protected function formatOutput(array $output, array $error): array
    {
        if(!empty($error)) {
            return $error;
        }

        $rules = [];

        foreach($output as $rule) {
            preg_match('/^\[ *([0-9]+)]/', $rule, $matches);
            $rules[str_replace($matches[0] . ' 22/tcp ALLOW IN ', '', $rule)] = (int)$matches[1];
        }

        return $rules;
    }


}
