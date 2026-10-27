<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BaseResource extends JsonResource
{
    protected function toCamelCaseArray($data)
    {
        if (is_array($data)) {
            $result = [];

            foreach ($data as $key => $value) {
                $camelKey = is_string($key) ? Str::camel($key) : $key;
                $result[$camelKey] = $this->toCamelCaseArray($value);
            }

            return $result;
        }

        return $data;
    }

    public function toArray(Request $request): array
    {
        $data = $this->raw($request);

        return $this->toCamelCaseArray($data);
    }

    protected function raw(Request $request): array
    {
        return parent::toArray($request);
    }
}