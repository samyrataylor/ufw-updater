<?php

namespace App\Processes\UFW\Actions;

use App\Types\Protocol;

class Allow extends UFWAction
{
    protected(set) Protocol $protocol;
    protected(set) int $port;
    protected(set) string $source;

    public function __construct(?string $source = null, ?int $port = null, ?Protocol $protocol = null)
    {
        if (!is_null($source)) {
            $this->source($source);
        }

        if (!is_null($port)) {
            $this->port($port);
        }

        if (!is_null($protocol)) {
            $this->protocol($protocol);
        }
    }

    public function rawAction(): string
    {
        return 'allow from {source} to any port {port} {protocol}';
    }

    public function actionPlaceholders(): array
    {
        return [
            'source'   => $this->source ?? 'any',
            'port'     => $this->port ?? 22,
            'protocol' => $this->getProtocol(),
        ];
    }

    public function getProtocol(): string
    {
        if (!isset($this->protocol) || $this->protocol->isEmpty()) {
            return '';
        }

        return 'proto '.$this->protocol;
    }

    public function protocol(Protocol $protocol): static
    {
        $this->protocol = $protocol;
        return $this;
    }

    public function source(string $source): static
    {
        $matched = preg_match(
            '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(?:\/(?:3[0-2]|[0-2]?[0-9]?))?$/',
            $source
        );

        if ($matched) {
            $this->source = $source;
        }
        return $this;
    }

    public function port(int $port): static
    {
        $this->port = $port;
        return $this;
    }
}
