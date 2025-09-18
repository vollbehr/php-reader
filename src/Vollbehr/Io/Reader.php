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
 * The \Vollbehr\Io\Reader class represents a character stream providing means to
 * read primitive types (string, integers, ...) from it.
 * @author Sven Vollbehr
 * @author Ryan Butterfield
 * @author Marc Bennewitz
 */
class Reader
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
     * Constructs the \Vollbehr\Io\Reader class with given open file descriptor.
     * @param resource $fd The file descriptor.
     * @throws Exception if given file descriptor is not valid
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
     * Checks whether there is more to be read from the stream. Returns
     * <var>true</var> if the end has not yet been reached; <var>false</var>
     * otherwise.
     * @throws Exception if an I/O error occurs
     */
    public function available(): bool
    {
        return $this->getOffset() < $this->getSize();
    }

    /**
     * Returns the current point of operation.
     * @throws Exception if an I/O error occurs
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
     * @throws Exception if an I/O error occurs
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
     * @return integer
     */
    public function getSize()
    {
        return $this->_size;
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
     * Jumps <var>size</var> amount of bytes in the stream.
     * @param integer $size The amount of bytes.
     * @throws Exception if <var>size</var> attribute is negative or if
     *  an I/O error occurs
     */
    public function skip($size): void
    {
        if ($size < 0) {

            throw new Exception('Size cannot be negative');
        }
        if ($size == 0) {
            return;
        }
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }
        fseek($this->_fd, $size, SEEK_CUR);
    }

    /**
     * Reads <var>length</var> amount of bytes from the stream.
     * @param integer $length The amount of bytes.
     * @throws Exception if <var>length</var> attribute is negative or
     *  if an I/O error occurs
     * @return string
     */
    public function read($length): string | false
    {
        if ($length < 0) {

            throw new Exception('Length cannot be negative');
        }
        if ($length == 0) {
            return '';
        }
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }

        $remaining = $this->getSize() - $this->getOffset();
        if ($remaining <= 0) {
            return '';
        }

        if ($length > $remaining) {
            $length = $remaining;
        }

        return fread($this->_fd, $length);
    }

    /**
     * Reads 1 byte from the stream and returns binary data as an 8-bit integer.
     * @throws Exception if an I/O error occurs
     */
    final public function readInt8(): int
    {
        $ord = ord($this->read(1));
        if ($ord > 127) {
            return -$ord - 2 * (128 - $ord);
        } else {
            return $ord;
        }
    }

    /**
     * Reads 1 byte from the stream and returns binary data as an unsigned 8-bit
     * integer.
     * @throws Exception if an I/O error occurs
     */
    final public function readUInt8(): int
    {
        return ord($this->read(1));
    }

    /**
     * Returns machine endian ordered binary data as signed 16-bit integer.
     * @param string $value The binary data string.
     * @return integer
     */
    private function _fromInt16(string | bool $value)
    {
        [, $int] = unpack('s*', $value);

        return $int;
    }

    /**
     * Reads 2 bytes from the stream and returns little-endian ordered binary
     * data as signed 16-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt16LE()
    {
        if ($this->_isBigEndian()) {
            return $this->_fromInt16(strrev($this->read(2)));
        } else {
            return $this->_fromInt16($this->read(2));
        }
    }

    /**
     * Reads 2 bytes from the stream and returns big-endian ordered binary data
     * as signed 16-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt16BE()
    {
        if ($this->_isLittleEndian()) {
            return $this->_fromInt16(strrev($this->read(2)));
        } else {
            return $this->_fromInt16($this->read(2));
        }
    }

    /**
     * Reads 2 bytes from the stream and returns machine ordered binary data
     * as signed 16-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt16()
    {
        return $this->_fromInt16($this->read(2));
    }

    /**
     * Returns machine endian ordered binary data as unsigned 16-bit integer.
     * @param string  $value The binary data string.
     * @param integer $order The byte order of the binary data string.
     * @return integer
     */
    private function _fromUInt16(string | bool $value, int $order = 0)
    {
        [, $int] = unpack(
            ($order == self::BIG_ENDIAN_ORDER ? 'n' :
            ($order == self::LITTLE_ENDIAN_ORDER ? 'v' : 'S')) . '*',
            $value
        );

        return $int;
    }

    /**
     * Reads 2 bytes from the stream and returns little-endian ordered binary
     * data as unsigned 16-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt16LE()
    {
        return $this->_fromUInt16($this->read(2), self::LITTLE_ENDIAN_ORDER);
    }

    /**
     * Reads 2 bytes from the stream and returns big-endian ordered binary data
     * as unsigned 16-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt16BE()
    {
        return $this->_fromUInt16($this->read(2), self::BIG_ENDIAN_ORDER);
    }

    /**
     * Reads 2 bytes from the stream and returns machine ordered binary data
     * as unsigned 16-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt16()
    {
        return $this->_fromUInt16($this->read(2), self::MACHINE_ENDIAN_ORDER);
    }

    /**
     * Returns machine endian ordered binary data as signed 24-bit integer.
     * @param string $value The binary data string.
     * @return integer
     */
    private function _fromInt24(string $value)
    {
        [, $int] = unpack('l*', $this->_isLittleEndian() ? ("\x00" . $value) : ($value . "\x00"));

        return $int;
    }

    /**
     * Reads 3 bytes from the stream and returns little-endian ordered binary
     * data as signed 24-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt24LE()
    {
        if ($this->_isBigEndian()) {
            return $this->_fromInt24(strrev($this->read(3)));
        } else {
            return $this->_fromInt24($this->read(3));
        }
    }

    /**
     * Reads 3 bytes from the stream and returns big-endian ordered binary data
     * as signed 24-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt24BE()
    {
        if ($this->_isLittleEndian()) {
            return $this->_fromInt24(strrev($this->read(3)));
        } else {
            return $this->_fromInt24($this->read(3));
        }
    }

    /**
     * Reads 3 bytes from the stream and returns machine ordered binary data
     * as signed 24-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt24()
    {
        return $this->_fromInt24($this->read(3));
    }

    /**
     * Returns machine endian ordered binary data as unsigned 24-bit integer.
     * @param string  $value The binary data string.
     * @param integer $order The byte order of the binary data string.
     * @return integer
     */
    private function _fromUInt24(string $value, int $order = 0)
    {
        [, $int] = unpack(
            ($order == self::BIG_ENDIAN_ORDER ? 'N' :
            ($order == self::LITTLE_ENDIAN_ORDER ? 'V' : 'L')) . '*',
            $this->_isLittleEndian() ? ("\x00" . $value) : ($value . "\x00")
        );

        return $int;
    }

    /**
     * Reads 3 bytes from the stream and returns little-endian ordered binary
     * data as unsigned 24-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt24LE()
    {
        return $this->_fromUInt24($this->read(3), self::LITTLE_ENDIAN_ORDER);
    }

    /**
     * Reads 3 bytes from the stream and returns big-endian ordered binary data
     * as unsigned 24-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt24BE()
    {
        return $this->_fromUInt24($this->read(3), self::BIG_ENDIAN_ORDER);
    }

    /**
     * Reads 3 bytes from the stream and returns machine ordered binary data
     * as unsigned 24-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt24()
    {
        return $this->_fromUInt24($this->read(3), self::MACHINE_ENDIAN_ORDER);
    }

    /**
     * Returns machine-endian ordered binary data as signed 32-bit integer.
     * @param string $value The binary data string.
     * @return integer
     */
    private function _fromInt32(string | bool $value)
    {
        [, $int] = unpack('l*', $value);

        return $int;
    }

    /**
     * Reads 4 bytes from the stream and returns little-endian ordered binary
     * data as signed 32-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt32LE()
    {
        if ($this->_isBigEndian()) {
            return $this->_fromInt32(strrev($this->read(4)));
        } else {
            return $this->_fromInt32($this->read(4));
        }
    }

    /**
     * Reads 4 bytes from the stream and returns big-endian ordered binary data
     * as signed 32-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt32BE()
    {
        if ($this->_isLittleEndian()) {
            return $this->_fromInt32(strrev($this->read(4)));
        } else {
            return $this->_fromInt32($this->read(4));
        }
    }

    /**
     * Reads 4 bytes from the stream and returns machine ordered binary data
     * as signed 32-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt32()
    {
        return $this->_fromInt32($this->read(4));
    }

    /**
     * Reads 4 bytes from the stream and returns little-endian ordered binary
     * data as unsigned 32-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt32LE()
    {
        if (PHP_INT_SIZE < 8) {
            [, $lo, $hi] = unpack('v*', $this->read(4));

            return $hi * (0xffff + 1) + $lo; // eq $hi << 16 | $lo
        } else {
            [, $int] = unpack('V*', $this->read(4));

            return $int;
        }
    }

    /**
     * Reads 4 bytes from the stream and returns big-endian ordered binary data
     * as unsigned 32-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt32BE()
    {
        if (PHP_INT_SIZE < 8) {
            [, $hi, $lo] = unpack('n*', $this->read(4));

            return $hi * (0xffff + 1) + $lo; // eq $hi << 16 | $lo
        } else {
            [, $int] = unpack('N*', $this->read(4));

            return $int;
        }
    }

    /**
     * Reads 4 bytes from the stream and returns machine ordered binary data
     * as unsigned 32-bit integer.
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readUInt32()
    {
        if (PHP_INT_SIZE < 8) {
            [, $hi, $lo] = unpack('L*', $this->read(4));

            return $hi * (0xffff + 1) + $lo; // eq $hi << 16 | $lo
        } else {
            [, $int] = unpack('L*', $this->read(4));

            return $int;
        }
    }

    /**
     * Reads 8 bytes from the stream and returns little-endian ordered binary
     * data as 64-bit float.
     * {@internal PHP does not support 64-bit integers as the long
     * integer is of 32-bits but using aritmetic operations it is implicitly
     * converted into floating point which is of 64-bits long.}}
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt64LE(): int | float
    {
        [, $lolo, $lohi, $hilo, $hihi] = unpack('v*', $this->read(8));

        return ($hihi * (0xffff + 1) + $hilo) * (0xffffffff + 1) +
            ($lohi * (0xffff + 1) + $lolo);
    }

    /**
     * Reads 8 bytes from the stream and returns big-endian ordered binary data
     * as 64-bit float.
     * {@internal PHP does not support 64-bit integers as the long integer is of
     * 32-bits but using aritmetic operations it is implicitly converted into
     * floating point which is of 64-bits long.}}
     * @throws Exception if an I/O error occurs
     * @return integer
     */
    final public function readInt64BE(): int | float
    {
        [, $hihi, $hilo, $lohi, $lolo] = unpack('n*', $this->read(8));

        return ($hihi * (0xffff + 1) + $hilo) * (0xffffffff + 1) +
            ($lohi * (0xffff + 1) + $lolo);
    }

    /**
     * Returns machine endian ordered binary data as a 32-bit floating point
     * number as defined by IEEE 754.
     * @param string $value The binary data string.
     * @return float
     */
    private function _fromFloat(string | bool $value)
    {
        [, $float] = unpack('f', $value);

        return $float;
    }

    /**
     * Reads 4 bytes from the stream and returns little-endian ordered binary
     * data as a 32-bit float point number as defined by IEEE 754.
     * @throws Exception if an I/O error occurs
     * @return float
     */
    final public function readFloatLE()
    {
        if ($this->_isBigEndian()) {
            return $this->_fromFloat(strrev($this->read(4)));
        } else {
            return $this->_fromFloat($this->read(4));
        }
    }

    /**
     * Reads 4 bytes from the stream and returns big-endian ordered binary data
     * as a 32-bit float point number as defined by IEEE 754.
     * @throws Exception if an I/O error occurs
     * @return float
     */
    final public function readFloatBE()
    {
        if ($this->_isLittleEndian()) {
            return $this->_fromFloat(strrev($this->read(4)));
        } else {
            return $this->_fromFloat($this->read(4));
        }
    }

    /**
     * Returns machine endian ordered binary data as a 64-bit floating point
     * number as defined by IEEE754.
     * @param string $value The binary data string.
     * @return float
     */
    private function _fromDouble(string | bool $value)
    {
        [, $double] = unpack('d', $value);

        return $double;
    }

    /**
     * Reads 8 bytes from the stream and returns little-endian ordered binary
     * data as a 64-bit floating point number as defined by IEEE 754.
     * @throws Exception if an I/O error occurs
     * @return float
     */
    final public function readDoubleLE()
    {
        if ($this->_isBigEndian()) {
            return $this->_fromDouble(strrev($this->read(8)));
        } else {
            return $this->_fromDouble($this->read(8));
        }
    }

    /**
     * Reads 8 bytes from the stream and returns big-endian ordered binary data
     * as a 64-bit float point number as defined by IEEE 754.
     * @throws Exception if an I/O error occurs
     * @return float
     */
    final public function readDoubleBE()
    {
        if ($this->_isLittleEndian()) {
            return $this->_fromDouble(strrev($this->read(8)));
        } else {
            return $this->_fromDouble($this->read(8));
        }
    }

    /**
     * Reads <var>length</var> amount of bytes from the stream and returns
     * binary data as string. Removes terminating zero.
     * @param integer $length   The amount of bytes.
     * @param string  $charList The list of characters you want to strip.
     * @throws Exception if <var>length</var> attribute is negative or
     *  if an I/O error occurs
     */
    final public function readString8($length, $charList = "\0"): string
    {
        return rtrim($this->read($length), $charList);
    }

    /**
     * Reads <var>length</var> amount of bytes from the stream and returns
     * binary data as multibyte Unicode string. Removes terminating zero.
     * The byte order is possibly determined from the byte order mark included
     * in the binary data string. The order parameter is updated if the BOM is
     * found.
     * @param integer $length    The amount of bytes.
     * @param integer $order     The endianess of the string.
     * @param integer $trimOrder Whether to remove the byte order mark read the
     *                string.
     * @throws Exception if <var>length</var> attribute is negative or
     *  if an I/O error occurs
     */
    final public function readString16($length, &$order = null, $trimOrder = false): string
    {
        $value = $this->read($length);

        if (strlen($value) < 2) {
            return '';
        }

        if (ord($value[0]) == 0xfe && ord($value[1]) == 0xff) {
            $order = self::BIG_ENDIAN_ORDER;
            if ($trimOrder) {
                $value = substr($value, 2);
            }
        }
        if (ord($value[0]) == 0xff && ord($value[1]) == 0xfe) {
            $order = self::LITTLE_ENDIAN_ORDER;
            if ($trimOrder) {
                $value = substr($value, 2);
            }
        }

        while (str_ends_with($value, "\0\0")) {
            $value = substr($value, 0, -2);
        }

        return $value;
    }

    /**
     * Reads <var>length</var> amount of bytes from the stream and returns
     * binary data as hexadecimal string having high nibble first.
     * @param integer $length The amount of bytes.
     * @throws Exception if <var>length</var> attribute is negative or
     *  if an I/O error occurs
     * @return string
     */
    final public function readHHex($length)
    {
        [$hex] = unpack('H*0', $this->read($length));

        return $hex;
    }

    /**
     * Reads <var>length</var> amount of bytes from the stream and returns
     * binary data as hexadecimal string having low nibble first.
     * @param integer $length The amount of bytes.
     * @throws Exception if <var>length</var> attribute is negative or
     *  if an I/O error occurs
     * @return string
     */
    final public function readLHex($length)
    {
        [$hex] = unpack('h*0', $this->read($length));

        return $hex;
    }

    /**
     * Reads 16 bytes from the stream and returns the little-endian ordered
     * binary data as mixed-ordered hexadecimal GUID string.
     * @throws Exception if an I/O error occurs
     */
    final public function readGuid(): ?string
    {
        $C     = @unpack('V1V/v2v/N2N', $this->read(16));
        [$hex] = @unpack('H*0', pack('NnnNN', $C['V'], $C['v1'], $C['v2'], $C['N1'], $C['N2']));

        /* Fixes a bug in PHP versions earlier than Jan 25 2006 */
        if (implode('', unpack('H*', pack('H*', 'a'))) === 'a00') {
            $hex = substr((string) $hex, 0, -1);
        }

        return preg_replace('/^(.{8})(.{4})(.{4})(.{4})/', '\\1-\\2-\\3-\\4-', (string) $hex);
    }

    /**
     * Resets the stream. Attempts to reset it in some way appropriate to the
     * particular stream, for example by repositioning it to its starting point.
     * @throws Exception if an I/O error occurs
     */
    public function reset(): void
    {
        if ($this->_fd === null) {

            throw new Exception('Cannot operate on a closed stream');
        }
        fseek($this->_fd, 0);
    }

    /**
     * Closes the stream. Once a stream has been closed, further calls to read
     * methods will throw an exception. Closing a previously-closed stream,
     * however, has no effect.
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
            self::$_endianess = $this->_fromInt32("\x01\x00\x00\x00") == 1 ?
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
