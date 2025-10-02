<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496\Box;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Sound Media Header Box_ contains general presentation information,
 * independent of the coding, for audio media. This header is used for all
 * tracks containing audio.
 * @author Sven Vollbehr
 */
final class Smhd extends \Vollbehr\Media\Iso14496\FullBox
{
    private float $_balance;
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        $this->_balance = ((($tmp = $this->_reader->readUInt16BE()) >> 8) & 0xff) +
            (float)('0.' . ($tmp & 0xff));
        $this->_reader->skip(2);
    }
    /**
     * Returns the number that places mono audio tracks in a stereo space; 0 is
     * center (the normal value); full left is -1.0 and full right is 1.0.
     */
    public function getBalance(): float
    {
        return $this->_balance;
    }
    /**
     * Sets the number that places mono audio tracks in a stereo space; 0 is
     * center (the normal value); full left is -1.0 and full right is 1.0.
     * @param integer $balance The balance.
     */
    public function setBalance(float $balance): void
    {
        $this->_balance = $balance;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 4;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        @[, $balanceDecimals] = explode('.', $this->_balance);
        $writer->writeInt16BE(floor($this->_balance) << 8 | $balanceDecimals)
               ->writeInt16BE(0);
    }
}
