<?php

declare(strict_types=1);

namespace App\Infrastructure\Favorite\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FavoriteGifModel extends Model
{
    protected $table = 'favorite_gifs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'gif_id',
        'alias',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
        );
    }
}

?>
