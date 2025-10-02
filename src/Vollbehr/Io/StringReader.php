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
 * The \Vollbehr\Io\StringReader represents a character stream whose source is
 * a string.
 * @author Sven Vollbehr
 */
class StringReader extends Reader
{
    /**
     * Constructs the \Vollbehr\Io\StringReader class with given source string.
     * @param string $data The string to use as the source.
     * @param integer $length If the <var>length</var> argument is given,
     *  reading will stop after <var>length</var> bytes have been read or
     *  the end of string is reached, whichever comes first.
     * @throws Exception if an I/O error occurs
     */
    public function __construct($data, $length = null)
    {
        if (($this->_fd = fopen('php://memory', 'w+b')) === false) {

            throw new Exception('Unable to open php://memory stream');
        }
        if ($data !== null && is_string($data)) {
            if ($length === null) {
                $length = strlen($data);
            }
            if (($this->_size = fwrite($this->_fd, $data, $length)) === false) {

                throw new Exception('Unable to write data to php://memory stream');
            }
            fseek($this->_fd, 0);
        }
    }

    /**
     * Returns the string representation of this class.
     */
    public function toString()
    {
        $offset = $this->getOffset();
        $this->setOffset(0);
        $data = $this->read($this->getSize());
        $this->setOffset($offset);

        return $data;
    }

    /**
     * Closes the file descriptor.
     */
    public function __destruct()
    {
        $this->close();
    }
}
