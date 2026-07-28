<?php

declare(strict_types=1);

namespace App\Application\Gif\Search;

use InvalidArgumentException;

final readonly class SearchGifsInput
{
    private const DEFAULT_LIMIT = 10;

    private const DEFAULT_OFFSET = 0;

    private const MAX_LIMIT = 50;

    private const MAX_OFFSET = 4999;

    private const MAX_QUERY_LENGTH = 50;

    public string $query;

    public int $limit;

    public int $offset;

    public function __construct(string $query, ?int $limit = null, ?int $offset = null)
    {
        $normalizedQuery = trim($query);
        $resolvedLimit = $limit ?? self::DEFAULT_LIMIT;
        $resolvedOffset = $offset ?? self::DEFAULT_OFFSET;

        if ($normalizedQuery === '') {
            throw new InvalidArgumentException('Query cannot be empty.');
        }

        if (mb_strlen($normalizedQuery) > self::MAX_QUERY_LENGTH) {
            throw new InvalidArgumentException('Query cannot exceed '.self::MAX_QUERY_LENGTH.' characters.');
        }

        if ($resolvedLimit < 1 || $resolvedLimit > self::MAX_LIMIT) {
            throw new InvalidArgumentException('Limit must be between 1 and '.self::MAX_LIMIT.'.');
        }

        if ($resolvedOffset < 0 || $resolvedOffset > self::MAX_OFFSET) {
            throw new InvalidArgumentException('Offset must be between 0 and '.self::MAX_OFFSET.'.');
        }

        $this->query = $normalizedQuery;
        $this->limit = $resolvedLimit;
        $this->offset = $resolvedOffset;
    }
}
