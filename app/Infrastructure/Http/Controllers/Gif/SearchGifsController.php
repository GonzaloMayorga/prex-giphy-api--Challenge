<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Gif;

use App\Application\Gif\Search\SearchGifs;
use App\Application\Gif\Search\SearchGifsInput;
use App\Infrastructure\Http\Requests\Gif\SearchGifsRequest;
use App\Infrastructure\Http\Resources\Gif\GifResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class SearchGifsController
{
    public function __construct(
        private SearchGifs $searchGifs,
    ) {}

    public function __invoke(
        SearchGifsRequest $request,
    ): AnonymousResourceCollection {
        $validated = $request->validated();

        $input = new SearchGifsInput(
            query: (string) $validated['query'],
            limit: array_key_exists('limit', $validated)
                ? (int) $validated['limit']
                : null,
            offset: array_key_exists('offset', $validated)
                ? (int) $validated['offset']
                : null,
        );

        $result = $this->searchGifs->execute($input);

        return GifResource::collection(
            $result->items()
        )->additional([
            'meta' => [
                'query' => $input->query,
                'limit' => $input->limit,
                'offset' => $input->offset,
                'count' => count($result),
            ],
        ]);
    }
}
