<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Audit\Security;

use App\Infrastructure\Audit\Security\SensitiveDataRedactor;
use PHPUnit\Framework\TestCase;

final class SensitiveDataRedactorTest extends TestCase
{
    public function test_it_redacts_sensitive_values_recursively(): void
    {
        $redactor = new SensitiveDataRedactor(
            sensitiveKeys: [
                'password',
                'access_token',
                'client_secret',
            ],
        );

        $result = $redactor->redact([
            'email' => 'challenge@example.com',
            'password' => 'secret-password',
            'data' => [
                'access_token' => 'secret-token',
                'client_secret' => 'secret-client',
                'name' => 'Challenge User',
            ],
        ]);

        self::assertSame([
            'email' => 'challenge@example.com',
            'password' => '[REDACTED]',
            'data' => [
                'access_token' => '[REDACTED]',
                'client_secret' => '[REDACTED]',
                'name' => 'Challenge User',
            ],
        ], $result);
    }

    public function test_keys_are_compared_case_insensitively(): void
    {
        $redactor = new SensitiveDataRedactor([
            'password',
        ]);

        $result = $redactor->redact([
            'PASSWORD' => 'secret-password',
        ]);

        self::assertSame([
            'PASSWORD' => '[REDACTED]',
        ], $result);
    }
}

?>
