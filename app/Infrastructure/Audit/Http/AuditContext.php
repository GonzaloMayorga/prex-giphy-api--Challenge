<?php

declare(strict_types=1);

namespace App\Infrastructure\Audit\Http;

final class AuditContext
{
    public const STARTED_AT_NANOSECONDS =
        '_audit_started_at_nanoseconds';

    public const USER_ID =
        '_audit_user_id';

    private function __construct()
    {
    }
}

?>
