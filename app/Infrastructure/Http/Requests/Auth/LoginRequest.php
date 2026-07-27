<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'bail',
                'required',
                'string',
                'email',
                'max:255',
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be valid.',
            'email.max' => 'The email cannot exceed 255 characters.',

            'password.required' => 'The password is required.',
            'password.string' => 'The password must be a string.',
            'password.max' => 'The password cannot exceed 255 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');

        if (!is_string($email)) {
            return;
        }

        $this->merge([
            'email' => mb_strtolower(
                trim($email)
            ),
        ]);
    }
}

?>