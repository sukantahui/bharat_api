<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ConvertsCamelToSnake;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    use ConvertsCamelToSnake;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'department_id' => [
                'required',
                'numeric',
                'min:1',
                Rule::exists('departments', 'id')
            ],
            'designation_id' => [
                'required',
                'numeric',
                'min:1',
                Rule::exists('designations', 'id')
            ],
            'employee_name' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'mobile' => [
                'sometimes',
                'string',
                'max:15',
                'min:10',
                Rule::unique('employees', 'mobile')
            ],
            'email' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('employees', 'email')
            ],
        ];
    }

    protected function prepareForValidation()
    {
        // Convert camelCase to snake_case before validation
        $this->merge($this->convertCamelToSnake($this->all()));
    }
}
