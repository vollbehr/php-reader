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
 * The _Sample Size Box_ contains the sample count and a table giving the
 * size in bytes of each sample. This allows the media data itself to be
 * unframed. The total number of samples in the media is always indicated in the
 * sample count.
 * There are two variants of the sample size box. The first variant has a fixed
 * size 32-bit field for representing the sample sizes; it permits defining a
 * constant size for all samples in a track. The second variant permits smaller
 * size fields, to save space when the sizes are varying but small. One of these
 * boxes must be present; the first version is preferred for maximum
 * compatibility.
 * @author Sven Vollbehr
 */
final class Stsz extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var integer */
    private $_sampleSize;
    /** @var Array */
    private $_sampleSizeTable = [];
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_sampleSize = $this->_reader->readUInt32BE();
        $sampleCount       = $this->_reader->readUInt32BE();
        if ($this->_sampleSize == 0) {
            for ($i = 1; $i <= $sampleCount; $i++) {
                $this->_sampleSizeTable[$i] = $this->_reader->readUInt32BE();
            }
        }
    }
    /**
     * Returns the default sample size. If all the samples are the same size,
     * this field contains that size value. If this field is set to 0, then the
     * samples have different sizes, and those sizes are stored in the sample
     * size table.
     * @return integer
     */
    public function getSampleSize()
    {
        return $this->_sampleSize;
    }
    /**
     * Sets the default sample size. If all the samples are the same size,
     * this field contains that size value. If this field is set to 0, then the
     * samples have different sizes, and those sizes are stored in the sample
     * size table.
     * @param integer $sampleSize The default sample size.
     */
    public function setSampleSize($sampleSize): void
    {
        $this->_sampleSize = $sampleSize;
    }
    /**
     * Returns an array of sample sizes specifying the size of a sample, indexed
     * by its number.
     * @return Array
     */
    public function getSampleSizeTable()
    {
        return $this->_sampleSizeTable;
    }
    /**
     * Sets an array of sample sizes specifying the size of a sample, indexed
     * by its number.
     * @param Array $sampleSizeTable The array of sample sizes.
     */
    public function setSampleSizeTable($sampleSizeTable): void
    {
        $this->_sampleSizeTable = $sampleSizeTable;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): int | float
    {
        return parent::getHeapSize() + 8 +
            ($this->_sampleSize == 0 ? count($this->_sampleSizeTable) * 4 : 0);
    }

    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $writer->writeUInt32BE($this->_sampleSize);
        $writer->writeUInt32BE($entryCount = count($this->_sampleSizeTable));
        if ($this->_sampleSize == 0) {
            for ($i = 1; $i <= $entryCount; $i++) {
                $writer->writeUInt32BE($this->_sampleSizeTable[$i]);
            }
        }
    }
}