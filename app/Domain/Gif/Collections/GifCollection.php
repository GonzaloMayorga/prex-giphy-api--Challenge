<?php

declare(strict_types=1);

namespace App\Domain\Gif\Collections;

use App\Domain\Gif\Entities\Gif;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Gif>
 */
final readonly class GifCollection implements Countable, IteratorAggregate
{
    /**
     * @var list<Gif>
     */
    private array $items;

    /**
     * @param  array<array-key, mixed>  $items
     */
    public function __construct(array $items)
    {
        $validatedItems = [];

        foreach ($items as $item) {
            if (! $item instanceof Gif) {
                throw new InvalidArgumentException(
                    'All items must be instances of Gif.',
                );
            }

            $validatedItems[] = $item;
        }

        $this->items = $validatedItems;
    }

    /**
     * @return list<Gif>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, Gif>
     */
    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }
}
