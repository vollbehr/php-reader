<?php

declare(strict_types=1);

namespace Vollbehr\Media\Asf\Object;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * The _Compatibility Object_ is reserved for future use.
 * @author Sven Vollbehr
 */
final class Compatibility extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var integer */
    private $_profile;

    /** @var integer */
    private $_mode;

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader = null, &$options = [])
    {
        parent::__construct($reader, $options);
        if ($reader === null) {
            return;
        }

        $this->_profile = $this->_reader->readUInt8();
        $this->_mode    = $this->_reader->readUInt8();
    }

    /**
     * Returns the profile field. This field is reserved and is set to 2.
     * @return integer
     */
    public function getProfile()
    {
        return $this->_profile;
    }

    /**
     * Returns the profile field. This field is reserved and is set to 2.
     * @param integer $profile The profile.
     */
    public function setProfile($profile): void
    {
        $this->_profile = $profile;
    }

    /**
     * Returns the mode field. This field is reserved and is set to 1.
     * @return integer
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Sets the mode field. This field is reserved and is set to 1.
     * @param integer $mode The mode.
     */
    public function setMode($mode): void
    {
        $this->_mode = $mode;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $this->setSize(24 /* for header */ + 2);
        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt8($this->_profile)
               ->writeUInt8($this->_mode);
    }
}
