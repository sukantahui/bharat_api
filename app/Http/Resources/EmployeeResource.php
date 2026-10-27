<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */


    public function toArray(Request $request): array
    {
        return [
            'employeeId'=>$this->id,
            'employeeName' => $this->employee_name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'department' => [
                'departmentId' => $this->department->id ?? null,
                'name' => $this->department->department_name ?? null,
            ],
            'designation' => [
                'designationId' => $this->designation->id ?? null,
                'name' => $this->designation->designation_name ?? null,
            ],
            //'createdAt' => $this->created_at?$this->created_at->format('Y-m-d H:i:s'):null,
            //'updatedAt' => $this->updated_at?$this->updated_at->format('Y-m-d H:i:s'):null,
        ];
    }
}
