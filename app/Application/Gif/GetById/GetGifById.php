<?php

declare(strict_types=1);

namespace App\Application\Gif\GetById;

use App\Domain\Gif\Entities\Gif;
use App\Domain\Gif\Exceptions\GifNotFoundException;
use App\Domain\Gif\Ports\GifProvider;

final readonly class GetGifById
{
    public function __construct(
        private GifProvider $gifProvider,
    ) {}

    public function execute(
        GetGifByIdInput $input,
    ): Gif {
        $gif = $this->gifProvider->getById(
            $input->id
        );

        if ($gif === null) {
            throw GifNotFoundException::withId(
                $input->id
            );
        }

        return $gif;
    }
}
