<?php

declare(strict_types=1);

namespace Vollbehr\Io;

/**
 * PHP Reader
 * @package   \Vollbehr\Io
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * The \Vollbehr\Io\Writer class represents a character stream providing means to
 * write primitive types (string, integers, ...) to it.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 */
class Writer
{
    public const MACHINE_ENDIAN_ORDER = 0;
    public const LITTLE_ENDIAN_ORDER  = 1;
    public const BIG_ENDIAN_ORDER     = 2;

    /** The endianess of the current machine. */
    private static int $_endianess = 0;

    /**
     * The resource identifier of the stream.
     * @var resource
     */
    protected $_fd;
    /**
     * Size of the underlying stream.
     * @var integer
     */
    protected $_size = 0;
    /**
     * Constructs the \Vollbehr\Io\Writer class with given open file descriptor.
     * @param resource $fd The file descriptor.
     * @throws Exception if file descriptor is not valid
     */
    public function __construct($fd)
    {
        if (!is_resource($fd) ||
                get_resource_type($fd) !== 'stream') {

            throw new Exception('Invalid resource type (only resources of type stream are supported)');
        }

        $this->_fd = $fd;

        $offset = $this->getOffset();
        fseek($this->_fd, 0, SEEK_END);
        $this->_size = ftell($this->_fd);
        fseek($this->_fd, $offset);
    }

    /**
     * Default destructor.
     */
    public function __destruct()
    {
    }

    /**
     * Returns the current point of operation.
     * @throws Exception if the stream is closed
     * @return integer
     */
    public function getOffset(): int | false
    {
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }

        return ftell($this->_fd);
    }

    /**
     * Sets the point of operation, ie the cursor offset value. The offset may
     * also be set to a negative value when it is interpreted as an offset from
     * the end of the stream instead of the beginning.
     * @param integer $offset The new point of operation.
     * @throws Exception if the stream is closed
     */
    public function setOffset($offset): void
    {
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }
        fseek($this->_fd, $offset < 0 ? $this->getSize() + $offset : $offset);
    }

    /**
     * Returns the stream size in bytes.
     * @throws Exception if the stream is closed
     * @return integer
     */
    public function getSize()
    {
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }

        return $this->_size;
    }

    /**
     * Sets the stream size in bytes, and truncates if required.
     * @param integer $size The new size
     * @throws Exception if the stream is closed
     */
    public function setSize($size): void
    {
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }
        ftruncate($this->_fd, $size);
    }

    /**
     * Returns the underlying stream file descriptor.
     * @return resource
     */
    public function getFileDescriptor()
    {
        return $this->_fd;
    }

    /**
     * Writes <var>value</var> up to <var>length</var> bytes to the stream.
     * @param string  $value  The value to write to the stream.
     * @param integer $length The number of bytes to write. Defaults to the
     *  length of the given value.
     * @throws Exception if the stream is closed
     */
    public function write($value, $length = null): static
    {
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }
        if ($length === null) {
            $length = strlen($value);
        }
        fwrite($this->_fd, $value, $length);
        $this->_size += $length;

        return $this;
    }

    /**
     * Writes an 8-bit integer as binary data to the stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeInt8($value): static
    {
        return $this->write(pack('c*', $value));
    }

    /**
     * Writes an unsigned 8-bit integer as binary data to the stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeUInt8($value): static
    {
        return $this->write(pack('C*', $value));
    }

    /**
     * Returns signed 16-bit integer as machine endian ordered binary data.
     * @param integer $value The input value.
     */
    private function _toInt16($value): string
    {
        return pack('s*', $value);
    }

    /**
     * Writes a signed 16-bit integer as little-endian ordered binary data to
     * the stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeInt16LE($value): static
    {
        if ($this->_isLittleEndian()) {
            return $this->write(strrev($this->_toInt16($value)));
        } else {
            return $this->write($this->_toInt16($value));
        }
    }

    /**
     * Returns signed 16-bit integer as big-endian ordered binary data to the
     * stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeInt16BE($value): static
    {
        if ($this->_isBigEndian()) {
            return $this->write(strrev($this->_toInt16($value)));
        } else {
            return $this->write($this->_toInt16($value));
        }
    }

    /**
     * Writes unsigned 16-bit integer as little-endian ordered binary data
     * to the stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeUInt16LE($value): static
    {
        return $this->write(pack('v*', $value));
    }

    /**
     * Writes unsigned 16-bit integer as big-endian ordered binary data to the
     * stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeUInt16BE($value): static
    {
        return $this->write(pack('n*', $value));
    }

    /**
     * Returns signed 32-bit integer as machine-endian ordered binary data.
     * @param integer $value The input value.
     */
    private function _toInt32($value): string
    {
        return pack('l*', $value);
    }

    /**
     * Writes signed 32-bit integer as little-endian ordered binary data to the
     * stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeInt32LE($value): static
    {
        if ($this->_isLittleEndian()) {
            return $this->write(strrev($this->_toInt32($value)));
        } else {
            return $this->write($this->_toInt32($value));
        }
    }

    /**
     * Writes signed 32-bit integer as big-endian ordered binary data to the
     * stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeInt32BE($value): static
    {
        if ($this->_isBigEndian()) {
            return $this->write(strrev($this->_toInt32($value)));
        } else {
            return $this->write($this->_toInt32($value));
        }
    }

    /**
     * Writes unsigned 32-bit integer as little-endian ordered binary data to
     * the stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeUInt32LE($value): static
    {
        return $this->write(pack('V*', $value));
    }

    /**
     * Writes unsigned 32-bit integer as big-endian ordered binary data to the
     * stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeUInt32BE($value): static
    {
        return $this->write(pack('N*', $value));
    }

    /**
     * Writes 64-bit float as little-endian ordered binary data string to the
     * stream.
     * @param  integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeInt64LE($value): static
    {
        return $this->write(pack('V*', $value & 0xffffffff, $value / (0xffffffff + 1)));
    }

    /**
     * Writes 64-bit float as big-endian ordered binary data string to the
     * stream.
     * @param integer $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeInt64BE($value): static
    {
        return $this->write(pack('N*', $value / (0xffffffff + 1), $value & 0xffffffff));
    }

    /**
     * Returns a floating point number as machine endian ordered binary data.
     * @param float $value The input value.
     */
    private function _toFloat($value): string
    {
        return pack('f*', $value);
    }

    /**
     * Writes a floating point number as little-endian ordered binary data to
     * the stream.
     * @param float $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeFloatLE($value): static
    {
        if ($this->_isLittleEndian()) {
            return $this->write(strrev($this->_toFloat($value)));
        } else {
            return $this->write($this->_toFloat($value));
        }
    }

    /**
     * Writes a floating point number as big-endian ordered binary data to the
     * stream.
     * @param float $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeFloatBE($value): static
    {
        if ($this->_isBigEndian()) {
            return $this->write(strrev($this->_toFloat($value)));
        } else {
            return $this->write($this->_toFloat($value));
        }
    }

    /**
     * Writes string as binary data padded to given length with zeros. If
     * <var>length</var> is smaller than the length of the string, it is
     * considered as the length of the padding.
     * @param string  $value   The input value.
     * @param integer $length  The length to which to pad the value.
     * @param string  $padding The padding character.
     * @throws Exception if the stream is closed
     */
    final public function writeString8($value, $length = null, $padding = "\0"): static
    {
        if ($length === null) {
            $length = strlen($value);
        }
        if ($length < ($tmp = strlen($value))) {
            $length = $tmp + $length;
        }

        return $this->write(str_pad($value, $length, $padding));
    }

    /**
     * Writes the multibyte string as binary data with given byte order mark
     * (BOM) and padded to given length with zeros. Length is given in unicode
     * characters so each character adds two zeros to the string. If length is
     * smaller than the length of the string, it is considered as the length of
     * the padding.
     * If byte order mark is <var>null</var> no mark is inserted to the binary
     * data.
     * @param string  $value   The input value.
     * @param integer $order   The byte order of the binary data string.
     * @param integer $length  The length to which to pad the value.
     * @param string  $padding The padding character.
     * @throws Exception if the stream is closed
     * @return string
     */
    final public function writeString16($value, $order = null, $length = null, $padding = "\0"): static
    {
        if ($length === null) {
            $length = (int)(strlen($value) / 2);
        }
        if ($length < ($tmp = strlen($value) / 2)) {
            $length = $tmp + $length;
        }
        if ($order == self::BIG_ENDIAN_ORDER &&
                !(ord($value[0]) == 0xfe && ord($value[1]) == 0xff)) {
            $value = 0xfeff . $value;
            $length++;
        }
        if ($order == self::LITTLE_ENDIAN_ORDER &&
                !(ord($value[0]) == 0xff && ord($value[1]) == 0xfe)) {
            $value = 0xfffe . $value;
            $length++;
        }

        return $this->write(str_pad($value, $length * 2, $padding));
    }

    /**
     * Writes hexadecimal string having high nibble first as binary data to the
     * stream.
     * @param string $value The input value.
     * @throws Exception if <var>length</var> attribute is negative or
     *  if the stream is closed
     */
    final public function writeHHex($value): static
    {
        return $this->write(pack('H*', $value));
    }

    /**
     * Writes hexadecimal string having low nibble first as binary data to the
     * stream.
     * @param string $value The input value.
     * @throws Exception if <var>length</var> attribute is negative or
     *  if the stream is closed
     */
    final public function writeLHex($value): static
    {
        return $this->write(pack('h*', $value));
    }

    /**
     * Writes big-endian ordered hexadecimal GUID string as little-endian
     * ordered binary data string to the stream.
     * @param string $value The input value.
     * @throws Exception if the stream is closed
     */
    final public function writeGuid($value): static
    {
        $C = preg_split('/-/', $value);

        return $this->write(pack(
            'V1v2N2',
            hexdec($C[0]),
            hexdec($C[1]),
            hexdec($C[2]),
            hexdec($C[3] . substr($C[4], 0, 4)),
            hexdec(substr($C[4], 4))
        ));
    }

    /**
     * Forces write of all buffered output to the underlying resource.
     * @throws Exception if the stream is closed
     */
    public function flush(): void
    {
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }
        fflush($this->_fd);
    }

    /**
     * Closes the stream. Once a stream has been closed, further calls to write
     * methods will throw an exception. Closing a previously-closed stream,
     * however, has no effect.
     * @throws Exception if the stream is closed
     */
    public function close(): void
    {
        if ($this->_fd !== null) {
            @fclose($this->_fd);
            $this->_fd = null;
        }
    }

    /**
     * Returns the current machine endian order.
     */
    private function _getEndianess(): int
    {
        if (self::$_endianess === 0) {
            self::$_endianess = $this->_toInt32("\x01\x00\x00\x00") == 1 ?
                self::LITTLE_ENDIAN_ORDER : self::BIG_ENDIAN_ORDER;
        }

        return self::$_endianess;
    }

    /**
     * Returns whether the current machine endian order is little endian.
     */
    private function _isLittleEndian(): bool
    {
        return $this->_getEndianess() == self::LITTLE_ENDIAN_ORDER;
    }

    /**
     * Returns whether the current machine endian order is big endian.
     */
    private function _isBigEndian(): bool
    {
        return $this->_getEndianess() == self::BIG_ENDIAN_ORDER;
    }

    /**
     * Magic function so that $obj->value will work.
     * @param string $name The field name.
     */
    public function __get(string $name)
    {
        if (method_exists($this, 'get' . ucfirst(strtolower($name)))) {
            return call_user_func([$this, 'get' . ucfirst(strtolower($name))]);
        } else {

            throw new Exception('Unknown field: ' . $name);
        }
    }

    /**
     * Magic function so that assignments with $obj->value will work.
     * @param string $name  The field name.
     * @param string $value The field value.
     */
    public function __set(string $name, $value)
    {
        if (method_exists($this, 'set' . ucfirst(strtolower($name)))) {
            call_user_func([$this, 'set' . ucfirst(strtolower($name))], $value);
        } else {

            throw new Exception('Unknown field: ' . $name);
        }
    }
}
