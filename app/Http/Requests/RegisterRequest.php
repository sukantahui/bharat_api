<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'email'        => 'required|string|max:200|unique:users,email',
            'password'     => 'required|min:8|confirmed',
            'user_type_id' => 'required|exists:user_types,id',
            'employee_id'  => 'nullable|exists:employees,id',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'        => 'Login username or email is required',
            'email.unique'          => 'This username or email is already registered',
            'password.required'     => 'Password is required',
            'password.min'          => 'Password must be at least 8 characters long',
            'password.confirmed'    => 'Password confirmation does not match',
            'user_type_id.required' => 'User role is required',
            'user_type_id.exists'   => 'Selected role does not exist',
            'employee_id.exists'    => 'Selected employee does not exist',
        ];
    }
}
