<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'api_interaction_logs',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'service',
                    150,
                );

                $table->string(
                    'http_method',
                    10,
                );

                $table->string(
                    'path',
                    2048,
                );

                $table
                    ->json('request_body')
                    ->nullable();

                $table->unsignedSmallInteger(
                    'response_status'
                );

                $table
                    ->json('response_body')
                    ->nullable();

                $table->string(
                    'origin_ip',
                    45,
                );

                $table
                    ->unsignedInteger('duration_ms')
                    ->default(0);

                $table
                    ->timestamp('created_at')
                    ->useCurrent();

                $table->index(
                    [
                        'service',
                        'created_at',
                    ],
                    'api_logs_service_created_index',
                );

                $table->index(
                    [
                        'user_id',
                        'created_at',
                    ],
                    'api_logs_user_created_index',
                );

                $table->index(
                    [
                        'response_status',
                        'created_at',
                    ],
                    'api_logs_status_created_index',
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'api_interaction_logs'
        );
    }
};

?>
