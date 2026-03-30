<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => ['required','string','max:50'],
            'last_name' => ['nullable','string','max:100'],
            'email' => ['required','email','max:150','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
            'ci' => ['nullable','string','max:50'],
            'roles' => ['sometimes','array'],
            'roles.*' => ['string','max:50'],
        ];
    }
}
