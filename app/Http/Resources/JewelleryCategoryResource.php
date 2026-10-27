<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JewelleryCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'categoryName'   => $this->category_name,
            'categoryCode'   => $this->category_code,
            'defaultHsnCode' => $this->default_hsn_code,
            'description'    => $this->description,
            'isActive'       => (bool) $this->is_active,
        ];
    }
}
