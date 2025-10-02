<?php

declare(strict_types=1);

namespace Vollbehr\Bit;

/**
 * PHP Reader
 * @package   \Vollbehr\Bit
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * A utility class to perform bit twiddling on integers.
 * @author Ryan Butterfield
 * @author Sven Vollbehr
 * @static
 */
final class Twiddling
{
    /**
     * Default private constructor for a static class.
     */
    private function __construct()
    {
    }

    /**
     * Sets a bit at a given position in an integer.
     * @param integer $integer  The value to manipulate.
     * @param integer $position The position of the bit to set.
     * @param boolean $on       Whether to enable or clear the bit.
     */
    public static function setBit($integer, $position, $on): int
    {
        return $on ? self::enableBit($integer, $position) :
            self::clearBit($integer, $position);
    }

    /**
     * Enables a bit at a given position in an integer.
     * @param integer $integer  The value to manipulate.
     * @param integer $position The position of the bit to enable.
     */
    public static function enableBit($integer, $position): int
    {
        return $integer | (1 << $position);
    }

    /**
     * Clears a bit at a given position in an integer.
     * @param integer $integer  The value to manipulate.
     * @param integer $position The position of the bit to clear.
     */
    public static function clearBit($integer, $position): int
    {
        return $integer & ~(1 << $position);
    }

    /**
     * Toggles a bit at a given position in an integer.
     * @param integer $integer  The value to manipulate.
     * @param integer $position The position of the bit to toggle.
     */
    public static function toggleBit($integer, $position): int
    {
        return $integer ^ (1 << $position);
    }

    /**
     * Tests a bit at a given position in an integer.
     * @param integer $integer  The value to test.
     * @param integer $position The position of the bit to test.
     */
    public static function testBit($integer, $position): bool
    {
        return ($integer & (1 << $position)) != 0;
    }

    /**
     * Sets a given set of bits in an integer.
     * @param integer $integer The value to manipulate.
     * @param integer $bits    The bits to set.
     * @param boolean $on      Whether to enable or clear the bits.
     */
    public static function setBits($integer, $bits, $on): int
    {
        return $on ? self::enableBits($integer, $bits) :
            self::clearBits($integer, $bits);
    }

    /**
     * Enables a given set of bits in an integer.
     * @param integer $integer The value to manipulate.
     * @param integer $bits    The bits to enable.
     */
    public static function enableBits($integer, $bits): int
    {
        return $integer | $bits;
    }

    /**
     * Clears a given set of bits in an integer.
     * @param integer $integer The value to manipulate.
     * @param integer $bits    The bits to clear.
     */
    public static function clearBits($integer, $bits): int
    {
        return $integer & ~$bits;
    }

    /**
     * Toggles a given set of bits in an integer.
     * @param integer $integer The value to manipulate.
     * @param integer $bits    The bits to toggle.
     */
    public static function toggleBits($integer, $bits): int
    {
        return $integer ^ $bits;
    }

    /**
     * Tests a given set of bits in an integer
     * returning whether all bits are set.
     * @param integer $integer The value to test.
     * @param integer $bits    The bits to test.
     */
    public static function testAllBits($integer, $bits): bool
    {
        return ($integer & $bits) == $bits;
    }

    /**
     * Tests a given set of bits in an integer
     * returning whether any bits are set.
     * @param integer $integer The value to test.
     * @param integer $bits    The bits to test.
     */
    public static function testAnyBits($integer, $bits): bool
    {
        return ($integer & $bits) != 0;
    }

    /**
     * Stores a value in a given range in an integer.
     * @param integer $integer The value to store into.
     * @param integer $start   The position to store from. Must be <= $end.
     * @param integer $end     The position to store to. Must be >= $start.
     * @param integer $value   The value to store.
     */
    public static function setValue($integer, $start, $end, $value): int
    {
        $range = self::normaliseRange((int) $start, (int) $end);
        if ($range === null) {
            return (int) $integer;
        }

        [$start, $end, $width] = $range;
        $baseMask = self::buildWidthMask($width);
        $rangeMask = $start === 0 ? $baseMask : $baseMask << $start;
        $clampedValue = ((int) $value) & $baseMask;
        $valuePortion = $start === 0 ? $clampedValue : ($clampedValue << $start);

        return (((int) $integer) & ~$rangeMask) | $valuePortion;
    }

    /**
     * Retrieves a value from a given range in an integer, inclusive.
     * @param integer $integer The value to read from.
     * @param integer $start   The position to read from. Must be <= $end.
     * @param integer $end     The position to read to. Must be >= $start.
     * @return integer
     */
    public static function getValue($integer, $start, $end): int
    {
        $range = self::normaliseRange((int) $start, (int) $end);
        if ($range === null) {
            return 0;
        }

        [$start, $end, $width] = $range;
        if ($width === 0) {
            return 0;
        }

        $baseMask = self::buildWidthMask($width);
        $rangeMask = $start === 0 ? $baseMask : $baseMask << $start;

        return ((((int) $integer) & $rangeMask) >> $start) & $baseMask;
    }

    /**
     * Returns an integer with all bits set from start to end.
     * @param integer $start The position to start setting bits from. Must
     *                       be <= $end.
     * @param integer $end   The position to stop setting bits. Must
     *                       be >= $start.
     * @return integer
     */
    public static function getMask($start, $end): int
    {
        $range = self::normaliseRange((int) $start, (int) $end);
        if ($range === null) {
            return 0;
        }

        [$start, $end, $width] = $range;
        $baseMask = self::buildWidthMask($width);

        return $start === 0 ? $baseMask : $baseMask << $start;
    }

    /**
     * Normalises bit range boundaries to the host integer size.
     * @return array{int,int,int}|null [start, end, width] or null if the range is empty
     */
    private static function normaliseRange(int $start, int $end): ?array
    {
        $bitSize = PHP_INT_SIZE * 8;
        $start = max(0, $start);
        if ($start > $end) {
            return null;
        }
        if ($start >= $bitSize) {
            return null;
        }

        $end = min($end, $bitSize - 1);
        if ($end < $start) {
            return null;
        }

        return [$start, $end, $end - $start + 1];
    }

    /**
     * Builds a mask with the requested width starting from bit zero.
     */
    private static function buildWidthMask(int $width): int
    {
        if ($width <= 0) {
            return 0;
        }
        $width = min($width, PHP_INT_SIZE * 8);
        $mask = 0;
        for ($i = 0; $i < $width; $i++) {
            $mask = ($mask << 1) | 1;
        }

        return $mask;
    }
}
