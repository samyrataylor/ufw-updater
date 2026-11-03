<?php

namespace App\Processes\UFW\Actions;

class Remove extends UFWAction
{
    public function __construct(
        protected(set) int $ruleNumber,
    ) {}

    public function actionPlaceholders(): array
    {
        return [
            'rule' => $this->ruleNumber,
        ];
    }

    public function rawAction(): string
    {
        return '--force delete {rule}';
    }

}
