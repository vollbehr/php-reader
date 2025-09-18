<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Example;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Tests\Example
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Validates the PHP snippets embedded in the documentation.
 *
 * @author Sven Vollbehr
 */
final class DocumentationExamplesTest extends TestCase
{
    /**
     * Compiles each PHP example extracted from the documentation.
     *
     * @param string $sourceFile The documentation file containing the example.
     * @param string $code       The PHP snippet under test.
     * @param int    $index      The snippet index within the documentation file.
     *
     * @return void
     */
    #[DataProvider('providePhpExamples')]
    public function testPhpExampleCompiles(string $sourceFile, string $code, int $index): void
    {
        $snippet    = str_starts_with(ltrim($code), '<?php') ? $code : "<?php\n" . $code;
        $tempHandle = tmpfile();

        if ($tempHandle === false) {
            self::fail('Unable to create a temporary file for linting.');
        }

        $metadata = stream_get_meta_data($tempHandle);
        fwrite($tempHandle, $snippet);
        fflush($tempHandle);

        $process = proc_open(
            escapeshellcmd(PHP_BINARY) . ' -l ' . escapeshellarg($metadata['uri']),
            [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes
        );

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        foreach ($pipes as $pipe) {
            fclose($pipe);
        }

        $exitCode = is_resource($process) ? proc_close($process) : 1;
        fclose($tempHandle);

        $message = sprintf(
            'Snippet %d in %s failed to compile.%s%s',
            $index,
            $sourceFile,
            $stdout !== '' ? "\nSTDOUT: {$stdout}" : '',
            $stderr !== '' ? "\nSTDERR: {$stderr}" : ''
        );

        self::assertSame(0, $exitCode, $message);
    }

    /**
     * Provides the PHP examples sourced from the documentation set.
     *
     * @return iterable<string, array{string,string,int}>
     */
    public static function providePhpExamples(): iterable
    {
        $docsDir = __DIR__ . '/../../docs';
        $examples = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($docsDir, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->getExtension() !== 'md') {
                continue;
            }

            $source = $fileInfo->getRealPath();
            if ($source === false) {
                continue;
            }

            $contents = file_get_contents($source);
            if ($contents === false) {
                continue;
            }

            if (!preg_match_all('/```php\n(.*?)```/s', $contents, $matches)) {
                continue;
            }

            foreach ($matches[1] as $index => $snippet) {
                $examples[sprintf('%s:%d', $source, $index)] = [$source, $snippet, $index];
            }
        }

        return $examples;
    }
}
