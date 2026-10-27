<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait ConvertsCamelToSnake
{
    protected function convertCamelToSnake($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = [];

        foreach ($data as $key => $value) {

            // convert key
            $newKey = is_string($key) ? Str::snake($key) : $key;

            // recursive conversion
            if (is_array($value)) {
                $value = $this->convertCamelToSnake($value);
            }

            $result[$newKey] = $value;
        }

        return $result;
    }
}