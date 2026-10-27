<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\UserTypeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'userName'    => $this->email,
            'name'        => $this->employee?->employee_name ?? $this->email,
            'email'       => $this->employee?->email ?? $this->email,
            'role'        => $this->role_name,
            'mobile'      => $this->employee?->mobile,
            'department'  => $this->employee?->department?->department_name ?? 'General',
            'designation' => $this->employee?->designation?->designation_name ?? 'Staff',
            'employeeId'  => $this->employee_id,
        ];
    }
}
