<?php

declare(strict_types=1);

namespace App\Application\Gif\Search;

use App\Domain\Gif\Collections\GifCollection;
use App\Domain\Gif\Ports\GifProvider;

final readonly class SearchGifs
{
    public function __construct(private GifProvider $gifProvider) {}

    public function execute(SearchGifsInput $input): GifCollection
    {
        return $this->gifProvider->search(
            query: $input->query,
            limit: $input->limit,
            offset: $input->offset
        );
    }
}
