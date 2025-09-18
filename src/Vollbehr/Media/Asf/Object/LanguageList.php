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
 * The _Language List Object_ contains an array of Unicode-based language
 * IDs. All other header objects refer to languages through zero-based positions
 * in this array.
 * @author Sven Vollbehr
 */
final class LanguageList extends \Vollbehr\Media\Asf\BaseObject
{
    /** @var Array */
    private $_languages = [];

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

        $languageIdRecordsCount = $this->_reader->readUInt16LE();
        for ($i = 0; $i < $languageIdRecordsCount; $i++) {
            $languageIdLength   = $this->_reader->readInt8();
            $languageId         = $this->_reader->readString16($languageIdLength);
            $this->_languages[] = iconv('utf-16le', (string) $this->getOption('encoding'), $languageId);
        }
    }

    /**
     * Returns the array of language ids.
     * @return Array
     */
    public function getLanguages()
    {
        return $this->_languages;
    }

    /**
     * Sets the array of language ids.
     * @param Array $languages The array of language ids.
     */
    public function setLanguages($languages): void
    {
        $this->_languages = $languages;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $languageIdRecordsCount  = count($this->_languages);
        $languageIdRecordsWriter = new \Vollbehr\Io\StringWriter();
        for ($i = 0; $i < $languageIdRecordsCount; $i++) {
            $languageIdRecordsWriter
                ->writeInt8(strlen($languageId = iconv(
                    (string) $this->getOption('encoding'),
                    'utf-16le',
                    (string) $this->_languages[$i]
                ) . "\0\0"))
                ->writeString16($languageId);
        }

        $this->setSize(24 /* for header */ + 2 + $languageIdRecordsWriter->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeUInt16LE($languageIdRecordsCount)
               ->write($languageIdRecordsWriter->toString());
    }
}
