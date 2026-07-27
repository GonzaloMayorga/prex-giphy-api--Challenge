<?php

declare(strict_types=1);

namespace App\Infrastructure\Audit\Security;

use InvalidArgumentException;

final readonly class SensitiveDataRedactor
{
    /**
     * @var array<string, true>
     */
    private array $sensitiveKeys;

    /**
     * @param list<string> $sensitiveKeys
     */
    public function __construct(
        array $sensitiveKeys,
        private string $replacement = '[REDACTED]',
    ) {
        if ($this->replacement === '') {
            throw new InvalidArgumentException(
                'The redacted replacement value cannot be empty.'
            );
        }

        $normalizedKeys = [];

        foreach ($sensitiveKeys as $key) {
            $normalizedKey = mb_strtolower(
                trim($key)
            );

            if ($normalizedKey !== '') {
                $normalizedKeys[$normalizedKey] = true;
            }
        }

        $this->sensitiveKeys = $normalizedKeys;
    }

    public function redact(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $redacted = [];

        foreach ($value as $key => $item) {
            $isSensitive = is_string($key)
                && isset(
                    $this->sensitiveKeys[
                        mb_strtolower($key)
                    ]
                );

            $redacted[$key] = $isSensitive
                ? $this->replacement
                : $this->redact($item);
        }

        return $redacted;
    }
}

?>
