<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests\Favorite;

use App\Domain\Favorite\Entities\FavoriteGif;
use Illuminate\Foundation\Http\FormRequest;

final class SaveFavoriteGifRequest extends FormRequest
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
            'gif_id' => [
                'bail',
                'required',
                'string',
                'max:'.FavoriteGif::MAX_GIF_ID_LENGTH,
            ],
            'alias' => [
                'bail',
                'required',
                'string',
                'max:'.FavoriteGif::MAX_ALIAS_LENGTH,
            ],
            'user_id' => [
                'bail',
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'gif_id.required' => 'The GIF ID is required.',
            'gif_id.string' => 'The GIF ID must be a string.',
            'gif_id.max' => sprintf(
                'The GIF ID cannot exceed %d characters.',
                FavoriteGif::MAX_GIF_ID_LENGTH,
            ),

            'alias.required' => 'The favorite alias is required.',
            'alias.string' => 'The favorite alias must be a string.',
            'alias.max' => sprintf(
                'The favorite alias cannot exceed %d characters.',
                FavoriteGif::MAX_ALIAS_LENGTH,
            ),

            'user_id.required' => 'The user ID is required.',
            'user_id.integer' => 'The user ID must be an integer.',
            'user_id.min' => 'The user ID must be greater than zero.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $gifId = $this->input('gif_id');
        $alias = $this->input('alias');

        if (is_string($gifId)) {
            $this->merge([
                'gif_id' => trim($gifId),
            ]);
        }

        if (is_string($alias)) {
            $normalizedAlias = preg_replace(
                '/\s+/u',
                ' ',
                trim($alias),
            );

            $this->merge([
                'alias' => is_string($normalizedAlias)
                    ? $normalizedAlias
                    : trim($alias),
            ]);
        }
    }
}
