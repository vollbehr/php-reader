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
 * The _Independent and Disposable Samples Box_ optional table answers
 * three questions about sample dependency:
 *   1) does this sample depend on others (is it an I-picture)?
 *   2) do no other samples depend on this one?
 *   3) does this sample contain multiple (redundant) encodings of the data at
 *      this time-instant (possibly with different dependencies)?
 * In the absence of this table:
 *   1) the sync sample table answers the first question; in most video codecs,
 *      I-pictures are also sync points,
 *   2) the dependency of other samples on this one is unknown.
 *   3) the existence of redundant coding is unknown.
 * When performing trick modes, such as fast-forward, it is possible to use the
 * first piece of information to locate independently decodable samples.
 * Similarly, when performing random access, it may be necessary to locate the
 * previous sync point or random access recovery point, and roll-forward from
 * the sync point or the pre-roll starting point of the random access recovery
 * point to the desired point. While rolling forward, samples on which no others
 * depend need not be retrieved or decoded.
 * The value of sampleIsDependedOn is independent of the existence of redundant
 * codings. However, a redundant coding may have different dependencies from the
 * primary coding; if redundant codings are available, the value of
 * sampleDependsOn documents only the primary coding.
 * A sample dependency Box may also occur in the
 * {@see \Vollbehr\Media\Iso14496\Box\Traf Track Fragment Box}.
 * @author Sven Vollbehr
 */
final class Sdtp extends \Vollbehr\Media\Iso14496\FullBox
{
    /** @var Array */
    private $_sampleDependencyTypeTable = [];
    /**
     * Constructs the class with given parameters and reads box related data
     * from the ISO Base Media file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);

        $data = $this->_reader->read($this->getOffset() + $this->getSize() -
             $this->_reader->getOffset());
        $dataSize = strlen((string) $data);
        for ($i = 1; $i <= $dataSize; $i++) {
            $this->_sampleDependencyTypeTable[$i] = ['sampleDependsOn' => (($tmp = ord($data[$i - 1])) >> 4) & 0x3,
                 'sampleIsDependedOn' => ($tmp >> 2) & 0x3,
                 'sampleHasRedundancy' => $tmp & 0x3];
        }
    }
    /**
     * Returns an array of values. Each entry is an array containing the
     * following keys.
     *   o sampleDependsOn -- takes one of the following four values:
     *     0: the dependency of this sample is unknown;
     *     1: this sample does depend on others (not an I picture);
     *     2: this sample does not depend on others (I picture);
     *     3: reserved
     *   o sampleIsDependedOn -- takes one of the following four values:
     *     0: the dependency of other samples on this sample is unknown;
     *     1: other samples depend on this one (not disposable);
     *     2: no other sample depends on this one (disposable);
     *     3: reserved
     *   o sampleHasRedundancy -- takes one of the following four values:
     *     0: it is unknown whether there is redundant coding in this sample;
     *     1: there is redundant coding in this sample;
     *     2: there is no redundant coding in this sample;
     *     3: reserved
     * @return Array
     */
    public function getSampleDependencyTypeTable()
    {
        return $this->_sampleDependencyTypeTable;
    }
    /**
     * Sets the array of values. Each entry must be an array containing the
     * following keys.
     *   o sampleDependsOn -- takes one of the following four values:
     *     0: the dependency of this sample is unknown;
     *     1: this sample does depend on others (not an I picture);
     *     2: this sample does not depend on others (I picture);
     *     3: reserved
     *   o sampleIsDependedOn -- takes one of the following four values:
     *     0: the dependency of other samples on this sample is unknown;
     *     1: other samples depend on this one (not disposable);
     *     2: no other sample depends on this one (disposable);
     *     3: reserved
     *   o sampleHasRedundancy -- takes one of the following four values:
     *     0: it is unknown whether there is redundant coding in this sample;
     *     1: there is redundant coding in this sample;
     *     2: there is no redundant coding in this sample;
     *     3: reserved
     * @param Array $sampleDependencyTypeTable The array of values
     */
    public function setSampleDependencyTypeTable($sampleDependencyTypeTable): void
    {
        $this->_sampleDependencyTypeTable = $sampleDependencyTypeTable;
    }
    /**
     * Returns the box heap size in bytes.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        return parent::getHeapSize() + count($this->_sampleDependencyTypeTable);
    }
    /**
     * Writes the box data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    protected function _writeData($writer): void
    {
        parent::_writeData($writer);
        $counter = count($this->_sampleDependencyTypeTable);
        for ($i = 1; $i <= $counter; $i++) {
            $writer->write(chr(
                (($this->_sampleDependencyTypeTable[$i]
                    ['sampleDependsOn'] & 0x3) << 4) |
                (($this->_sampleDependencyTypeTable[$i]
                    ['sampleIsDependedOn'] & 0x3) << 2) |
                (($this->_sampleDependencyTypeTable[$i]
                    ['sampleHasRedundancy'] & 0x3))
            ));
        }
    }
}
