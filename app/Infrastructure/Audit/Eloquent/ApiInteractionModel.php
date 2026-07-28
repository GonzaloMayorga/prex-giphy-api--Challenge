<?php

declare(strict_types=1);

namespace App\Infrastructure\Audit\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ApiInteractionModel extends Model
{
    public $timestamps = false;

    protected $table = 'api_interaction_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'service',
        'http_method',
        'path',
        'request_body',
        'response_status',
        'response_body',
        'origin_ip',
        'duration_ms',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'request_body' => 'array',
            'response_status' => 'integer',
            'response_body' => 'array',
            'duration_ms' => 'integer',
            'created_at' => 'immutable_datetime',
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
