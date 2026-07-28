<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Favorite\Save;

use App\Application\Favorite\Save\SaveFavoriteGif;
use App\Application\Favorite\Save\SaveFavoriteGifInput;
use App\Domain\Favorite\Entities\FavoriteGif;
use App\Domain\Favorite\Exceptions\FavoriteGifAlreadyExistsException;
use App\Domain\Favorite\Exceptions\FavoriteGifOwnershipException;
use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Exceptions\GifNotFoundException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Fakes\Favorite\FakeFavoriteGifRepository;
use Tests\Fakes\Gif\FakeGifProvider;

final class SaveFavoriteGifTest extends TestCase
{
    public function test_it_saves_a_favorite_gif(): void
    {
        $provider = new FakeGifProvider;

        $provider->willReturnGifById(
            new Gif(
                id: 'abc123',
                title: 'Funny cat',
                originalUrl: 'https://example.com/original.gif',
            )
        );

        $repository = new FakeFavoriteGifRepository;
        $repository->willReportExisting(false);

        $expectedFavorite = FavoriteGif::reconstitute(
            id: 50,
            userId: 10,
            gifId: 'abc123',
            alias: 'My favorite cat',
            createdAt: new DateTimeImmutable(
                '2026-07-28T01:00:00+00:00'
            ),
            updatedAt: new DateTimeImmutable(
                '2026-07-28T01:00:00+00:00'
            ),
        );

        $repository->willReturnSaved(
            $expectedFavorite
        );

        $useCase = new SaveFavoriteGif(
            favoriteRepository: $repository,
            gifProvider: $provider,
        );

        $result = $useCase->execute(
            new SaveFavoriteGifInput(
                authenticatedUserId: 10,
                requestedUserId: 10,
                gifId: '  abc123  ',
                alias: '  My   favorite   cat  ',
            )
        );

        self::assertSame(
            $expectedFavorite,
            $result,
        );

        self::assertSame(
            10,
            $repository->receivedExistsUserId,
        );

        self::assertSame(
            'abc123',
            $repository->receivedExistsGifId,
        );

        self::assertSame(
            'abc123',
            $provider->receivedId,
        );

        self::assertNotNull(
            $repository->receivedFavorite
        );

        self::assertSame(
            'My favorite cat',
            $repository->receivedFavorite->alias(),
        );

        self::assertNull(
            $repository->receivedFavorite->id(),
        );
    }

    public function test_it_rejects_saving_for_another_user(): void
    {
        $provider = new FakeGifProvider;
        $repository = new FakeFavoriteGifRepository;

        $useCase = new SaveFavoriteGif(
            favoriteRepository: $repository,
            gifProvider: $provider,
        );

        $this->expectException(
            FavoriteGifOwnershipException::class
        );

        $useCase->execute(
            new SaveFavoriteGifInput(
                authenticatedUserId: 10,
                requestedUserId: 20,
                gifId: 'abc123',
                alias: 'My favorite',
            )
        );
    }

    public function test_it_rejects_a_duplicate_favorite(): void
    {
        $provider = new FakeGifProvider;

        $repository = new FakeFavoriteGifRepository;
        $repository->willReportExisting(true);

        $useCase = new SaveFavoriteGif(
            favoriteRepository: $repository,
            gifProvider: $provider,
        );

        $this->expectException(
            FavoriteGifAlreadyExistsException::class
        );

        try {
            $useCase->execute(
                new SaveFavoriteGifInput(
                    authenticatedUserId: 10,
                    requestedUserId: 10,
                    gifId: 'abc123',
                    alias: 'My favorite',
                )
            );
        } finally {
            self::assertNull(
                $provider->receivedId
            );

            self::assertNull(
                $repository->receivedFavorite
            );
        }
    }

    public function test_it_rejects_a_nonexistent_gif(): void
    {
        $provider = new FakeGifProvider;
        $provider->willReturnGifById(null);

        $repository = new FakeFavoriteGifRepository;
        $repository->willReportExisting(false);

        $useCase = new SaveFavoriteGif(
            favoriteRepository: $repository,
            gifProvider: $provider,
        );

        $this->expectException(
            GifNotFoundException::class
        );

        try {
            $useCase->execute(
                new SaveFavoriteGifInput(
                    authenticatedUserId: 10,
                    requestedUserId: 10,
                    gifId: 'missing',
                    alias: 'Missing GIF',
                )
            );
        } finally {
            self::assertSame(
                'missing',
                $provider->receivedId,
            );

            self::assertNull(
                $repository->receivedFavorite
            );
        }
    }

    public function test_it_rejects_an_empty_alias(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'The favorite alias cannot be empty.'
        );

        new SaveFavoriteGifInput(
            authenticatedUserId: 10,
            requestedUserId: 10,
            gifId: 'abc123',
            alias: '   ',
        );
    }
}
