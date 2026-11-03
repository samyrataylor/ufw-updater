<?php

namespace App\Processes;

class Dig extends SystemProcess
{
    public function __construct(
        protected(set) string $domain,
    ) {}

    public static function rawCommand(): string
    {
        return 'dig {domain} A +short';
    }

    public function placeholders(): array
    {
        return [
            'domain' => $this->domain,
        ];
    }

    protected function formatOutput(array $output, array $error): mixed
    {
        return $output[0];
    }


}
