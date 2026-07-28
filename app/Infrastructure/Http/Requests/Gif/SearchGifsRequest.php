<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests\Gif;

use Illuminate\Foundation\Http\FormRequest;

final class SearchGifsRequest extends FormRequest
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
            'query' => [
                'bail',
                'required',
                'string',
                'max:50',
            ],
            'limit' => [
                'sometimes',
                'integer',
                'between:1,50',
            ],
            'offset' => [
                'sometimes',
                'integer',
                'between:0,4999',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'query.required' => 'The search query is required.',
            'query.string' => 'The search query must be a string.',
            'query.max' => 'The search query cannot exceed 50 characters.',

            'limit.integer' => 'The limit must be an integer.',
            'limit.between' => 'The limit must be between 1 and 50.',

            'offset.integer' => 'The offset must be an integer.',
            'offset.between' => 'The offset must be between 0 and 4999.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $query = $this->query('query');

        if (! is_string($query)) {
            return;
        }

        $this->merge([
            'query' => trim($query),
        ]);
    }
}
