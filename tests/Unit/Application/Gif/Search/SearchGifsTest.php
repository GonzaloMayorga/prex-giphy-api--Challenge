<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Gif\Search;

use App\Application\Gif\Search\SearchGifs;
use App\Application\Gif\Search\SearchGifsInput;
use App\Domain\Gif\Entities\Gif;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\Gif\FakeGifProvider;

final class SearchGifsTest extends TestCase
{
    public function test_it_searches_gifs_using_the_provider(): void
    {
        $expectedGif = new Gif(
            id: '123',
            title: 'Dogs',
            originalUrl: 'https://example.com/Dogs.gif',
            username: 'gonzalo',
        );

        $provider = new FakeGifProvider;
        $provider->willReturnSearchResults([$expectedGif]);

        $useCase = new SearchGifs($provider);

        $result = $useCase->execute(new SearchGifsInput(
            query: 'dogs',
            limit: 10,
            offset: 0
        ));

        self::assertCount(1, $result);
        self::assertSame($expectedGif, $result->items()[0]);

        self::assertSame('dogs', $provider->receivedQuery);
        self::assertSame(10, $provider->receivedLimit);
        self::assertSame(0, $provider->receivedOffset);
    }

    public function test_it_uses_default_pagination_values(): void
    {
        $provider = new FakeGifProvider;
        $provider->willReturnSearchResults([]);

        $useCase = new SearchGifs($provider);

        $result = $useCase->execute(
            new SearchGifsInput(query: 'dogs')
        );

        self::assertTrue($result->isEmpty());
        self::assertSame(10, $provider->receivedLimit);
        self::assertSame(0, $provider->receivedOffset);
    }

    public function test_it_normalizes_the_search_query(): void
    {
        $provider = new FakeGifProvider;

        $useCase = new SearchGifs($provider);

        $useCase->execute(
            new SearchGifsInput(query: '   metalcore   ')
        );

        self::assertSame(
            'metalcore',
            $provider->receivedQuery,
        );
    }

    public function test_it_rejects_an_empty_query(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Query cannot be empty.'
        );

        new SearchGifsInput(query: '   ');
    }

    public function test_it_rejects_a_limit_lower_than_one(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Limit must be between 1 and 50.'
        );

        new SearchGifsInput(
            query: 'cats',
            limit: 0,
        );
    }

    public function test_it_rejects_a_limit_greater_than_fifty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Limit must be between 1 and 50.'
        );

        new SearchGifsInput(
            query: 'cats',
            limit: 51,
        );
    }

    public function test_it_rejects_a_negative_offset(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Offset must be between 0 and 4999.'
        );

        new SearchGifsInput(
            query: 'cats',
            offset: -1,
        );
    }

    public function test_it_rejects_an_offset_greater_than_4999(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Offset must be between 0 and 4999.'
        );

        new SearchGifsInput(
            query: 'cats',
            offset: 5000,
        );
    }

    public function test_it_rejects_a_query_longer_than_fifty_characters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Query cannot exceed 50 characters.'
        );

        new SearchGifsInput(
            query: str_repeat('a', 51),
        );
    }
}
