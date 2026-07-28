<?php

declare(strict_types=1);

namespace Tests\Architecture;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class LayerDependencyTest extends TestCase
{
    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function layerRules(): iterable
    {
        yield 'domain remains framework independent' => [
            self::appDirectory('Domain'),
            [
                'Illuminate\\',
                'Laravel\\',
                'App\\Application\\',
                'App\\Infrastructure\\',
                'App\\Models\\',
            ],
        ];

        yield 'application does not depend on infrastructure' => [
            self::appDirectory('Application'),
            [
                'Illuminate\\',
                'Laravel\\',
                'App\\Infrastructure\\',
                'App\\Models\\',
            ],
        ];
    }

    /**
     * @param  list<string>  $forbiddenDependencies
     */
    #[DataProvider('layerRules')]
    public function test_layer_does_not_use_forbidden_dependencies(
        string $directory,
        array $forbiddenDependencies,
    ): void {
        foreach ($this->phpFiles($directory) as $file) {
            $contents = file_get_contents($file);

            self::assertIsString(
                $contents,
                sprintf(
                    'The file "%s" could not be read.',
                    $file,
                ),
            );

            foreach ($forbiddenDependencies as $dependency) {
                self::assertStringNotContainsString(
                    $dependency,
                    $contents,
                    sprintf(
                        'The file "%s" contains the forbidden dependency "%s".',
                        $file,
                        $dependency,
                    ),
                );
            }
        }
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS,
            )
        );

        $files = [];

        foreach ($iterator as $file) {
            if (
                ! $file instanceof SplFileInfo
                || ! $file->isFile()
                || $file->getExtension() !== 'php'
            ) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }

    private static function appDirectory(string $path): string
    {
        return dirname(__DIR__, 2).'/app/'.$path;
    }
}
