<?php

namespace App\Http\Requests;

use App\Traits\ConvertsCamelToSnake;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class StoreDepartmentRequest extends FormRequest
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

            'department_name' => 'required|string|max:255|unique:departments,department_name',
        
        ];
    }
    
    protected function prepareForValidation()
    {
        // Convert camelCase to snake_case before validation
        $this->merge($this->convertCamelToSnake($this->all()));
        // Ensure inforce has a default value if not set
    }
   


    
}
