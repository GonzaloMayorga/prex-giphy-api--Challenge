<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Gif\GetById;

use App\Application\Gif\GetById\GetGifById;
use App\Application\Gif\GetById\GetGifByIdInput;
use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Exceptions\GifNotFoundException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\Gif\FakeGifProvider;

final class GetGifByIdTest extends TestCase
{
    public function test_it_finds_a_gif_using_the_provider(): void
    {
        $expectedGif = new Gif(
            id: 'abc123',
            title: 'Funny cat',
            originalUrl: 'https://example.com/original.gif',
            previewUrl: 'https://example.com/preview.gif',
            username: 'cat-user',
        );

        $provider = new FakeGifProvider();
        $provider->willReturnGifById($expectedGif);

        $useCase = new GetGifById($provider);

        $result = $useCase->execute(
            new GetGifByIdInput('abc123')
        );

        self::assertSame($expectedGif, $result);
        self::assertSame(
            'abc123',
            $provider->receivedId,
        );
    }

    public function test_it_normalizes_the_id(): void
    {
        $expectedGif = new Gif(
            id: 'abc123',
            title: 'Funny cat',
            originalUrl: 'https://example.com/original.gif',
        );

        $provider = new FakeGifProvider();
        $provider->willReturnGifById($expectedGif);

        $useCase = new GetGifById($provider);

        $useCase->execute(
            new GetGifByIdInput('   abc123   ')
        );

        self::assertSame(
            'abc123',
            $provider->receivedId,
        );
    }

    public function test_it_throws_when_the_gif_does_not_exist(): void
    {
        $provider = new FakeGifProvider();
        $provider->willReturnGifById(null);

        $useCase = new GetGifById($provider);

        $this->expectException(
            GifNotFoundException::class
        );

        $this->expectExceptionMessage(
            'The Gif with ID "missing" was not found.'
        );

        $useCase->execute(
            new GetGifByIdInput('missing')
        );
    }

    public function test_it_rejects_an_empty_id(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'The GIF ID cannot be empty.'
        );

        new GetGifByIdInput('   ');
    }

    public function test_it_rejects_an_excessively_long_id(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'The GIF ID cannot exceed 100 characters.'
        );

        new GetGifByIdInput(
            str_repeat('a', 101)
        );
    }
}