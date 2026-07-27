<?php

declare(strict_types=1);

namespace Tests\Fakes\Gif;

use App\Domain\Gif\Collections\GifCollection;
use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Ports\GifProvider;
use Throwable;

final class FakeGifProvider implements GifProvider
{
    /**
     * @var list<Gif>
     */
    private array $searchResults = [];

    private ?Gif $gifById = null;

    private ?Throwable $searchException = null;
    private ?Throwable $getByIdException = null;

    public ?string $receivedQuery = null;
    public ?int $receivedLimit = null;
    public ?int $receivedOffset = null;
    public ?string $receivedId = null;

    /**
     * @param list<Gif> $gifs
     */
    public function willReturnSearchResults(array $gifs): void
    {
        $this->searchResults = $gifs;
        $this->searchException = null;
    }

    public function willReturnGifById(?Gif $gif): void
    {
        $this->gifById = $gif;
        $this->getByIdException = null;
    }

    public function willFailSearchWith(
        Throwable $exception,
    ): void {
        $this->searchException = $exception;
    }

    public function willFailGetByIdWith(
        Throwable $exception,
    ): void {
        $this->getByIdException = $exception;
    }

    public function search(
        string $query,
        int $limit,
        int $offset,
    ): GifCollection {
        $this->receivedQuery = $query;
        $this->receivedLimit = $limit;
        $this->receivedOffset = $offset;

        if ($this->searchException !== null) {
            throw $this->searchException;
        }

        return new GifCollection(
            $this->searchResults
        );
    }

    public function getById(string $id): ?Gif
    {
        $this->receivedId = $id;

        if ($this->getByIdException !== null) {
            throw $this->getByIdException;
        }

        return $this->gifById;
    }
}