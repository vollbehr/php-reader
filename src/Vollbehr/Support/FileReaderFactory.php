<?php

declare(strict_types=1);

namespace Vollbehr\Support;

use Vollbehr\Io\FileReader;

/**
 * PHP Reader
 *
 * @package   \Vollbehr\Support
 * @copyright (c) 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * Creates file reader instances using optional default modes.
 *
 * @author Sven Vollbehr
 */
final class FileReaderFactory
{
    /**
     * Initialises the factory with an optional default file mode.
     *
     * @param string|null $defaultMode Default file open mode.
     */
    public function __construct(private readonly ?string $defaultMode = null)
    {
    }

    /**
     * Opens a file using the configured default mode when one is not supplied.
     *
     * @param string      $path Path to the file that should be opened.
     * @param string|null $mode Optional explicit file mode.
     *
     * @return FileReader
     */
    public function open(string $path, ?string $mode = null): FileReader
    {
        return new FileReader($path, $mode ?? $this->defaultMode);
    }
}
