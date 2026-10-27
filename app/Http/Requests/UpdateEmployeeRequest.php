<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ConvertsCamelToSnake;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    use ConvertsCamelToSnake;

    protected int|string|null $employeeId;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Convert camelCase to snake_case before validation
        $this->merge($this->convertCamelToSnake($this->all()));

        // Grab the employee ID from the route for use in rules
        $this->employeeId = $this->route('employeeId');
    }

    public function rules(): array
    {
        return [
            'department_id' => [
                'sometimes',
                'numeric',
                'min:1',
                Rule::exists('departments', 'id'),
            ],
            'designation_id' => [
                'sometimes',
                'numeric',
                'min:1',
                Rule::exists('designations', 'id'),
            ],
            'employee_name' => [
                'sometimes',
                'string',
                'max:255',
                'min:3',
            ],
            'mobile' => [
                'sometimes',
                'string',
                'max:15',
                'min:10',
                Rule::unique('employees', 'mobile')->ignore($this->employeeId),
            ],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($this->employeeId),
            ],
        ];
    }
}
