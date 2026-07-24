<?php

declare(strict_types=1);

namespace Tests\Fakes\Gif;

use App\Domain\Gif\Collections\GifCollection;
use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Ports\GifProvider;

final class FakeGifProvider implements GifProvider
{
    /**
     * @var list<Gif>
     */
    private array $searchResults = [];

    private ?Gif $gifById = null;

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
    }

    public function willReturnGifById(?Gif $gif): void
    {
        $this->gifById = $gif;
    }

    public function search(string $query, int $limit, int $offset): GifCollection
    {
        $this->receivedQuery = $query;
        $this->receivedLimit = $limit;
        $this->receivedOffset = $offset;

        return new GifCollection($this->searchResults);
    }

    public function getById(string $id): ?Gif
    {
        $this->receivedId = $id;

        return $this->gifById;
    }
}

?>