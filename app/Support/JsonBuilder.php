<?php

namespace App\Support;

use App\Contracts\Resource;

class JsonBuilder
{
    public static function toJson(iterable $data, int $options = 0): string
    {
        $json = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_decode(self::toJson($value, $options), true);
            }

            if (is_object($value) && in_array(Resource::class,  class_implements($value))) {
                $value = $value::RECURSE ? $value->toArray() : $value->toString();
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
