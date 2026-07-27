<?php

declare(strict_types=1);

namespace App\Domain\Audit\Ports;

use App\Domain\Audit\Entities\ApiInteraction;

interface ApiInteractionRepository
{
    public function record(
        ApiInteraction $interaction,
    ): void;
}

?>
