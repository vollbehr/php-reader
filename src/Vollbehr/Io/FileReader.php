<?php

declare(strict_types=1);

namespace Vollbehr\Io;

/**
 * PHP Reader
 * @package   \Vollbehr\Io
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The \Vollbehr\Io\FileReader represents a character stream whose source is
 * a file.
 * @author Sven Vollbehr
 */
class FileReader extends Reader
{
    /**
     * Constructs the \Vollbehr\Io\FileReader class with given path to the file. By
     * default the file is opened in read (rb) mode.
     * @param string $filename The path to the file.
     * @throws Exception if the file cannot be read
     */
    public function __construct($filename, $mode = null)
    {
        if ($mode === null) {
            $mode = 'rb';
        }
        if (!file_exists($filename) || !is_readable($filename) ||
            ($fd = fopen($filename, $mode)) === false) {

            throw new Exception('Unable to open file for reading: ' . $filename);
        }
        parent::__construct($fd);
    }

    /**
     * Closes the file descriptor.
     */
    public function __destruct()
    {
        $this->close();
    }
}
