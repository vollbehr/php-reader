<?php

declare(strict_types=1);

namespace Vollbehr\Media;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This class represents a file in Advanced Systems Format (ASF) as described in
 * {@see http://go.microsoft.com/fwlink/?LinkId=31334 The Advanced Systems
 * Format (ASF) Specification}. It is a file format that can contain various
 * types of information ranging from audio and video to script commands and
 * developer defined custom streams.
 * The ASF file consists of code blocks that are called content objects. Each
 * of these objects have a format of their own. They may contain other objects
 * or other specific data. Each supported object has been implemented as their
 * own classes to ease the correct use of the information.
 * @author Sven Vollbehr
 */
class Asf extends Asf\Object\Container
{
    private ?string $_filename = null;

    /**
     * Constructs the ASF class with given file and options.
     * The following options are currently recognized:
     *   o encoding -- Indicates the encoding that all the texts are presented
     *     with. By default this is set to utf-8. See the documentation of iconv
     *     for accepted values.
     *   o readonly -- Indicates that the file is read from a temporary location
     *     or another source it cannot be written back to.
     * @param string|resource|\Vollbehr\Io\Reader $filename The path to the file,
     *  file descriptor of an opened file, or a {@see \Vollbehr\Io\Reader} instance.
     * @param Array                          $options  The options array.
     */
    public function __construct($filename, $options = [])
    {
        if ($filename instanceof \Vollbehr\Io\Reader) {
            $this->_reader = &$filename;
        } else {

            try {
                $this->_reader = new \Vollbehr\Io\FileReader($filename);
            } catch (\Vollbehr\Io\Exception $e) {
                $this->_reader = null;

                throw new Asf\Exception($e->getMessage());
            }
            if (is_string($filename) && !isset($options['readonly'])) {
                $this->_filename = $filename;
            }
        }
        $this->setOptions($options);
        if ($this->getOption('encoding', null) === null) {
            $this->setOption('encoding', 'utf-8');
        }
        $this->setOffset(0);
        $this->setSize($this->_reader->getSize());
        $this->constructObjects(
            [self::HEADER => 'Header',
            self::DATA => 'Data',
            self::SIMPLE_INDEX => 'SimpleIndex',
            self::INDEX => 'Index',
            self::MEDIA_OBJECT_INDEX => 'MediaObjectIndex',
            self::TIMECODE_INDEX => 'TimecodeIndex']
        );
    }

    /**
     * Returns the mandatory header object contained in this file.
     * @return Asf\BaseObject\Header
     */
    public function getHeader()
    {
        $header = $this->getObjectsByIdentifier(self::HEADER);
        return $header[0];
    }

    /**
     * Returns the mandatory data object contained in this file.
     * @return Asf\BaseObject\Data
     */
    public function getData()
    {
        $data = $this->getObjectsByIdentifier(self::DATA);
        return $data[0];
    }

    /**
     * Returns an array of index objects contained in this file.
     * @return Array
     */
    public function getIndices()
    {
        return $this->getObjectsByIdentifier(self::SIMPLE_INDEX . '|' . self::INDEX . '|' .
             self::MEDIA_OBJECT_INDEX . '|' . self::TIMECODE_INDEX);
    }

    /**
     * Writes the changes to given media file. All object offsets must be
     * assumed to be invalid after the write operation.
     * @param string $filename The optional path to the file, use null to save
     *                         to the same file.
     */
    public function write($filename): void
    {
        if ($filename === null && ($filename = $this->_filename) === null) {
            throw new Asf\Exception('No file given to write to');
        } elseif ($filename !== null && $this->_filename !== null &&
                   realpath($filename) !== realpath($this->_filename) &&
                   !copy($this->_filename, $filename)) {
            throw new Asf\Exception('Unable to copy source to destination: ' .
                 realpath($this->_filename) . '->' . realpath($filename));
        }

        if (($fd = fopen($filename, file_exists($filename) ? 'r+b' : 'wb')) === false) {

            throw new Asf\Exception('Unable to open file for writing: ' . $filename);
        }

        $header          = $this->getHeader();
        $headerLengthOld = $header->getSize();
        $header->removeObjectsByIdentifier(Asf\BaseObject::PADDING);
        $header->headerExtension->removeObjectsByIdentifier(Asf\BaseObject::PADDING);

        $buffer = new \Vollbehr\Io\StringWriter();
        $header->write($buffer);
        $headerData      = $buffer->toString();
        $headerLengthNew = $header->getSize();

        // Fits right in
        if ($headerLengthOld != $headerLengthNew) {
            if ($headerLengthOld >= $headerLengthNew + 24 /* for header */) {
                $header->headerExtension->padding->setSize($headerLengthOld - $headerLengthNew);
                $buffer = new \Vollbehr\Io\StringWriter();
                $header->write($buffer);
                $headerData      = $buffer->toString();
                $headerLengthNew = $header->getSize();
            }

            // Must expand
            else {
                $header->headerExtension->padding->setSize(4096);
                $buffer = new \Vollbehr\Io\StringWriter();
                $header->write($buffer);
                $headerData      = $buffer->toString();
                $headerLengthNew = $header->getSize();

                fseek($fd, 0, SEEK_END);
                $oldFileSize = ftell($fd);
                ftruncate($fd, $newFileSize = $headerLengthNew - $headerLengthOld +
                     $oldFileSize);
                for ($i = 1, $cur = $oldFileSize; $cur > 0; $cur -= 1024, $i++) {
                    fseek($fd, -(($i * 1024) +
                          ($newFileSize - $oldFileSize)), SEEK_END);
                    $buffer = fread($fd, 1024);
                    fseek($fd, -($i * 1024), SEEK_END);
                    fwrite($fd, $buffer, 1024);
                }
            }
        }

        fseek($fd, 0);
        fwrite($fd, (string) $headerData, $headerLengthNew);
        fclose($fd);
    }
}
