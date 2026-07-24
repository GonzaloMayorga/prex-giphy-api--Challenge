<?php 

declare(strict_types=1);

namespace App\Domain\Gif\Ports;

use App\Domain\Gif\Collections\GifCollection;
use App\Domain\Gif\Entities\Gif;

interface GifProvider
{
    public function search(string $query, int $limit, int $offset): GifCollection;

    public function getById(string $id): ?Gif;
}