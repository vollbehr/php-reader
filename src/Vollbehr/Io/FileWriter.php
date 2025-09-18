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
 * The \Vollbehr\Io\FileWriter represents a character stream whose source is
 * a file.
 * @author Sven Vollbehr
 */
class FileWriter extends Writer
{
    /**
     * Constructs the \Vollbehr\Io\FileWriter class with given path to the file. By
     * default the file is opened in write mode without altering its content
     * (ie r+b mode if the file exists, and wb mode if not).
     * @param string $filename The path to the file.
     * @throws Exception if the file cannot be written
     */
    public function __construct($filename, $mode = null)
    {
        if ($mode === null) {
            $mode = file_exists($filename) ? 'r+b' : 'wb';
        }
        if (($fd = fopen($filename, $mode)) === false) {

            throw new Exception('Unable to open file for writing: ' . $filename);
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
