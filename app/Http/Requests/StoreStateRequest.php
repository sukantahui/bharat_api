<?php

namespace App\Http\Requests;
use App\Traits\ConvertsCamelToSnake;
use Illuminate\Foundation\Http\FormRequest;

class StoreStateRequest extends BaseRequest
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
            'state_code'     => ['required', 'numeric', 'max:500', 'unique:states,state_code'],
            'state_name'    => ['required','string','max:100','unique:states,state_name'],
        ];
    }
    
}
