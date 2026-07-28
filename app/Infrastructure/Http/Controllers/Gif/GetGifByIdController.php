<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers\Gif;

use App\Application\Gif\GetById\GetGifById;
use App\Application\Gif\GetById\GetGifByIdInput;
use App\Infrastructure\Http\Requests\Gif\GetGifByIdRequest;
use App\Infrastructure\Http\Resources\Gif\GifResource;

final readonly class GetGifByIdController
{
    public function __construct(
        private GetGifById $getGifById,
    ) {}

    public function __invoke(
        GetGifByIdRequest $request,
        string $id,
    ): GifResource {
        $validated = $request->validated();

        $input = new GetGifByIdInput(
            id: (string) ($validated['id'] ?? $id),
        );

        $gif = $this->getGifById->execute($input);

        return new GifResource($gif);
    }
}
