<?php

namespace App\Support;

use Illuminate\Contracts\Support\Jsonable;

class JsonBuilder
{
    public static function toJson(iterable $data, int $options = 0): string
    {
        $json = [];
        foreach ($data as $key => $value) {
            if (is_object($value) && class_implements($value, Jsonable::class)) {
                $value = $value->toJson($options);
            }

            if (is_array($value)) {
                $value = json_decode(self::toJson($value, $options), true);
            }

            $json[$key] = $value;
        }
        return json_encode($json, $options);
    }

    public static function toPrettyJson(iterable $data, int $options = 0): string
    {
        return static::toJson($data, $options | JSON_PRETTY_PRINT);
    }

    public static function toCompactJson(iterable $data, int $options = 0): string
    {
        return static::toJson($data, $options);
    }
}
