<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $user = User::where('id', $this->customer)->first();
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|unique:users,phone,' . $user->id,
            'address' => 'sometimes|nullable|string|max:255',
            'address2' => 'sometimes|nullable|string|max:255',
            'city' => 'sometimes|nullable|string|max:255',
            'country' => 'sometimes|nullable|string|max:255',
            'taxNum' => 'sometimes|nullable|string|max:255',
            'commercialRegister' => 'sometimes|nullable|string|max:255',
        ];
    }
}