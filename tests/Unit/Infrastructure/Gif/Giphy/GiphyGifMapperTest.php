<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Gif\Giphy;

use App\Infrastructure\Gif\Giphy\GiphyGifMapper;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

final class GiphyGifMapperTest extends TestCase
{
    public function test_it_maps_a_giphy_payload_to_a_gif(): void
    {
        $mapper = new GiphyGifMapper();

        $gif = $mapper->fromArray([
            'id' => 'abc123',
            'title' => 'Funny cat',
            'username' => 'cat-user',
            'images' => [
                'original' => [
                    'url' => 'https://example.com/original.gif',
                ],
                'preview_gif' => [
                    'url' => 'https://example.com/preview.gif',
                ],
            ],
        ]);

        self::assertSame('abc123', $gif->id());
        self::assertSame('Funny cat', $gif->title());
        self::assertSame(
            'https://example.com/original.gif',
            $gif->originalUrl(),
        );
        self::assertSame(
            'https://example.com/preview.gif',
            $gif->previewUrl(),
        );
        self::assertSame('cat-user', $gif->username());
    }

    public function test_it_uses_alt_text_when_title_is_empty(): void
    {
        $mapper = new GiphyGifMapper();

        $gif = $mapper->fromArray([
            'id' => 'abc123',
            'title' => '',
            'alt_text' => 'A cat jumping',
            'images' => [
                'original' => [
                    'url' => 'https://example.com/original.gif',
                ],
            ],
        ]);

        self::assertSame(
            'A cat jumping',
            $gif->title(),
        );
    }

    public function test_it_uses_a_default_title_when_none_exists(): void
    {
        $mapper = new GiphyGifMapper();

        $gif = $mapper->fromArray([
            'id' => 'abc123',
            'images' => [
                'original' => [
                    'url' => 'https://example.com/original.gif',
                ],
            ],
        ]);

        self::assertSame(
            'Untitled GIF',
            $gif->title(),
        );
    }

    public function test_it_uses_fixed_width_small_as_preview_fallback(): void
    {
        $mapper = new GiphyGifMapper();

        $gif = $mapper->fromArray([
            'id' => 'abc123',
            'title' => 'Funny cat',
            'images' => [
                'original' => [
                    'url' => 'https://example.com/original.gif',
                ],
                'fixed_width_small' => [
                    'url' => 'https://example.com/small.gif',
                ],
            ],
        ]);

        self::assertSame(
            'https://example.com/small.gif',
            $gif->previewUrl(),
        );
    }

    public function test_it_rejects_a_payload_without_an_id(): void
    {
        $this->expectException(
            UnexpectedValueException::class
        );

        $this->expectExceptionMessage(
            'The GIPHY field "id" is required.'
        );

        $mapper = new GiphyGifMapper();

        $mapper->fromArray([
            'title' => 'Funny cat',
            'images' => [
                'original' => [
                    'url' => 'https://example.com/original.gif',
                ],
            ],
        ]);
    }

    public function test_it_rejects_a_payload_without_an_original_url(): void
    {
        $this->expectException(
            UnexpectedValueException::class
        );

        $this->expectExceptionMessage(
            'The GIPHY field "images.original.url" is required.'
        );

        $mapper = new GiphyGifMapper();

        $mapper->fromArray([
            'id' => 'abc123',
            'title' => 'Funny cat',
            'images' => [],
        ]);
    }
}
