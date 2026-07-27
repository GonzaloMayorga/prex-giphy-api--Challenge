<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Audit\Record;

use App\Application\Audit\Record\RecordApiInteraction;
use App\Application\Audit\Record\RecordApiInteractionInput;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\Audit\FakeApiInteractionRepository;

final class RecordApiInteractionTest extends TestCase
{
    public function test_it_records_an_api_interaction(): void
    {
        $repository = new FakeApiInteractionRepository();

        $useCase = new RecordApiInteraction(
            $repository
        );

        $useCase->execute(
            new RecordApiInteractionInput(
                userId: 10,
                service: ' api.gifs.search ',
                httpMethod: 'get',
                path: 'api/gifs',
                requestBody: [
                    'query' => [
                        'query' => 'cats',
                    ],
                ],
                responseStatus: 200,
                responseBody: [
                    'data' => [],
                ],
                originIp: '203.0.113.10',
                durationMs: 25,
            )
        );

        $interaction = $repository
            ->recordedInteraction;

        self::assertNotNull($interaction);
        self::assertSame(1, $repository->recordCalls);

        self::assertSame(
            10,
            $interaction->userId(),
        );

        self::assertSame(
            'api.gifs.search',
            $interaction->service(),
        );

        self::assertSame(
            'GET',
            $interaction->httpMethod(),
        );

        self::assertSame(
            '/api/gifs',
            $interaction->path(),
        );

        self::assertSame(
            200,
            $interaction->responseStatus(),
        );
    }
}

?>
