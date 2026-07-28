<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests\Gif;

use Illuminate\Foundation\Http\FormRequest;

final class GetGifByIdRequest extends FormRequest
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
            'id' => [
                'bail',
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'The GIF ID is required.',
            'id.string' => 'The GIF ID must be a string.',
            'id.max' => 'The GIF ID cannot exceed 100 characters.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $id = $this->route('id');

        $this->merge([
            'id' => is_string($id)
                ? trim($id)
                : $id,
        ]);
    }
}
