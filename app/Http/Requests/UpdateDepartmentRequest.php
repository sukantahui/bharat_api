<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateDepartmentRequest extends FormRequest
{
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

    public function rules()
    {
        return [
            'departmentName' => [
                'required',
                'string',
                'max:255',
            ],
            'id' => [
                'required',
            ]


            // If you need to include other fields later:
            // 'status' => 'sometimes|boolean',
            // 'description' => 'nullable|string|max:500'
        ];
    }
    public function messages()
    {
        return [
            'departmentName.required' => 'The department name is required.',
            'departmentName.string'   => 'The department name must be a string.',
            'departmentName.max'      => 'The department name may not be greater than 255 characters.',
        ];
    }

}
