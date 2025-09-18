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
 * The _Degradation Priority Box_ contains the degradation priority of each
 * sample. Specifications derived from this define the exact meaning and
 * acceptable range of the priority field.
 * @author Sven Vollbehr
 */
final class Stdp extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_values = [];
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
    *
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        while ($this->_reader->getOffset() <
               $this->getOffset() + $this->getSize()) {
            $this->_values[] = ['priority' => $this->_reader->readUInt16BE()];
        }
    }
    /**
     * Returns an array of values. Each entry is an array containing the
     * following keys.
     *   o priority: specifies the degradation priority for each sample segment.
     * @return Array
     */
    public function getValues()
    {
        return $this->_values;
    }
    /**
     * Sets an array of values. Each entry must have an array containing the
     * following keys.
     *   o priority: specifies the degradation priority for each sample segment.
     * @param Array $values The array of values.
     */
    public function setValues($values): void
    {
        $this->_values = $values;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return parent::getHeapSize() + count($this->_values) * 2;
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $counter = count($this->_values);
        for ($i = 0; $i < $counter; $i++) {
            $writer->writeUInt16BE($this->_values[$i]['priority']);
        }
    }
}
