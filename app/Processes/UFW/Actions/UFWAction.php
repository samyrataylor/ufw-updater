<?php

namespace App\Processes\UFW\Actions;

use App\Processes\UFW\UFW;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class UFWAction extends UFW
{
    public function actionPlaceholders(): array
    {
        return [];
    }

    public function rawAction(): string
    {
        return $this->guessAction();
    }

    public function action(): string
    {
        $action = $this->rawAction();

        return strtr(
            $action,
            Arr::mapWithKeys(
                $this->actionPlaceholders(),
                fn(string $value, string $key) => ['{'.$key.'}' => $value]
            )
        );
    }


    protected function guessAction(): string
    {
        return Str::replace('_', ' ', Str::snake(class_basename($this)));
    }
}
