<?php

declare(strict_types=1);

namespace Tests\Fakes\Audit;

use App\Domain\Audit\Entities\ApiInteraction;
use App\Domain\Audit\Ports\ApiInteractionRepository;

final class FakeApiInteractionRepository implements
    ApiInteractionRepository
{
    public ?ApiInteraction $recordedInteraction = null;

    public int $recordCalls = 0;

    public function record(
        ApiInteraction $interaction,
    ): void {
        $this->recordedInteraction = $interaction;
        $this->recordCalls++;
    }
}

?>
