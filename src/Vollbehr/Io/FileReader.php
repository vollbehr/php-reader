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

        if (is_resource($filename)) {
            parent::__construct($filename);

            return;
        }

        if (!is_string($filename) || $filename === '') {
            throw new Exception('Unable to open file for reading: ' . (string) $filename);
        }

        $hasScheme = preg_match('/^[a-z0-9.+-]+:\/\//i', $filename) === 1;
        if (!$hasScheme && (!file_exists($filename) || !is_readable($filename))) {

            throw new Exception('Unable to open file for reading: ' . $filename);
        }

        $fd = @fopen($filename, $mode);
        if ($fd === false) {
            $error = error_get_last();
            $message = 'Unable to open file for reading: ' . $filename;
            if ($error !== null && isset($error['message'])) {
                $message .= ' (' . $error['message'] . ')';
            }

            throw new Exception($message);
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
