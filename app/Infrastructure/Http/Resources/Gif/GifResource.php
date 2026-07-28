<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Resources\Gif;

use App\Domain\Gif\Entities\Gif;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LogicException;

final class GifResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource instanceof Gif) {
            throw new LogicException('GifResource expects an instance of Gif.');
        }

        return [
            'id' => $this->resource->id(),
            'title' => $this->resource->title(),
            'original_url' => $this->resource->originalUrl(),
            'preview_url' => $this->resource->previewUrl(),
            'username' => $this->resource->username(),
        ];
    }
}
