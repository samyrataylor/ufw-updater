<?php

namespace App\Support;

/**
 * @method string path(string|array|null $path = null)
 * @method static string path(string|array|null $path = null)
 * @method self make()
 * @method static self make()
 */
class PathBuilder
{
    protected(set) string $pharPrefix = 'phar://';

    protected(set) string $basePath;

    protected(set) bool $phar;

    public function __construct()
    {
        $this->init()->booted();
    }

    public function init(): static
    {
        $this->basePath = app()->basePath();
        $this->phar = str_starts_with($this->basePath, $this->pharPrefix);

        return $this;
    }

    public function booted(): void
    {
        //
    }

    public function getBasePath(): string
    {
        if(!$this->phar) {
            return $this->basePath;
        }

        return dirname($this->removePrefix($this->basePath));
    }

    protected function removePrefix(string $path): string
    {
        return str_replace($this->pharPrefix, '', $path);
    }

    public function buildPath(string|array|null $path = null): string
    {
        if($path === null) {
            return $this->getBasePath();
        }

        return $this->getBasePath().DIRECTORY_SEPARATOR.$this->normalize($path);
    }

    public function normalize(string|array|null $path = null): string
    {
        if($path === null) {
            return '';
        }

        if(is_array($path)) {
            $normalized = '';
            foreach($path as $segment) {
                $normalized .= DIRECTORY_SEPARATOR.$this->normalize($segment);
            }
            return $normalized;
        }

        return preg_replace('/[\/\\\]/', DIRECTORY_SEPARATOR, $path);
    }

    public static function instance(): static
    {
        return new static();
    }

    public static function build(string|array|null $path = null): string
    {
        return static::instance()->buildPath($path);
    }

    public function __call(string $name, array $arguments): string|static
    {
        return match($name) {
            'path' => $this->buildPath(...$arguments),
            'make' => $this,
            default => throw new \BadMethodCallException()
        };
    }

    public static function __callStatic(string $name, array $arguments): string|static
    {
        return match($name) {
            'path' => new static()->buildPath(...$arguments),
            'make' => new static(),
            default => throw new \BadMethodCallException()
        };
    }
}
