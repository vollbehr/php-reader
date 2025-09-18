<?php

declare(strict_types=1);

namespace Vollbehr\Mime;

/**
 * PHP Reader
 * @package   \Vollbehr\Mime
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */

/**#@-*/

/**
 * This class is used to classify the given file using some magic bytes
 * characteristic to a particular file type. The classification information can
 * be a MIME type or just text describing the file.
 * This method is slower than determining the type by file suffix but on the
 * other hand reduces the risk of fail positives during the test.
 * The magic file consists of ASCII characters defining the magic numbers for
 * different file types. Each row has 4 to 5 columns, empty and commented lines
 * (those starting with a hash character) are ignored. Columns are described
 * below.
 *  o <b>1</b> -- byte number to begin checking from. '>' indicates a dependency
 *    upon the previous non-'>' line
 *  o <b>2</b> -- type of data to match. Can be one of following
 *    - _byte_ (single character)
 *    - _short_ (machine-order 16-bit integer)
 *    - _long_ (machine-order 32-bit integer)
 *    - _string_ (arbitrary-length string)
 *    - _date_ (long integer date (seconds since Unix epoch/1970))
 *    - _beshort_ (big-endian 16-bit integer)
 *    - _belong_ (big-endian 32-bit integer)
 *    - _bedate_ (big-endian 32-bit integer date)
 *    - _leshort_ (little-endian 16-bit integer)
 *    - _lelong_ (little-endian 32-bit integer)
 *    - _ledate_ (little-endian 32-bit integer date)
 *  o <b>3</b> -- contents of data to match
 *  o <b>4</b> -- file description/MIME type if matched
 *  o <b>5</b> -- optional MIME encoding if matched and if above was a MIME type
 * @author Sven Vollbehr
 */
final class Magic
{
    private readonly string | bool $_magic;

    /**
     * Reads the magic information from given magic file.
     * @param string $filename The path to the magic file.
     */
    public function __construct($filename)
    {
        $reader       = new \Vollbehr\Io\FileReader($filename);
        $this->_magic = $reader->read($reader->getSize());
    }

    /**
     * Returns the recognized MIME type/description of the given file. The type
     * is determined by the content using magic bytes characteristic for the
     * particular file type.
     * If the type could not be found, the function returns the default value,
     * or <var>null</var>.
     * @param string $filename The file path whose type to determine.
     * @param string $default  The default value.
     * @return string|false
     */
    public function getMimeType($filename, $default = null)
    {
        $reader = new \Vollbehr\Io\FileReader($filename);
        $parentOffset = 0;
        foreach (preg_split('/^/m', $this->_magic) as $line) {
            $chunks = [];
            if (in_array(preg_match(
                "/^(?P<Dependant>>?)(?P<Byte>\d+)\s+(?P<MatchType" .
                            ">\S+)\s+(?P<MatchData>\S+)(?:\s+(?P<MIMEType>[a-" .
                            "z]+\/[a-z-0-9]+)?(?:\s+(?P<Description>.?+))?)?$/",
                $line,
                $chunks
            ), [0, false], true)) {
                continue;
            }

            if ($chunks['Dependant'] !== '' && $chunks['Dependant'] !== '0') {
                $reader->setOffset($parentOffset);
                $reader->skip($chunks['Byte']);
            } else {
                $reader->setOffset($parentOffset = $chunks['Byte']);
            }

            $matchType = strtolower($chunks['MatchType']);
            $matchData = preg_replace(
                ['/\\\\ /', '/\\\\\\\\/', '/\\\\([0-7]{1,3})/e',
                   '/\\\\x([0-9A-Fa-f]{1,2})/e', '/0x([0-9A-Fa-f]+)/e'],
                [' ', '\\\\',
                   'pack("H*", base_convert("$1", 8, 16));',
                   'pack("H*", "$1");', 'hexdec("$1");'],
                $chunks['MatchData']
            );

            $data = match ($matchType) {
                // single character
                'byte' => $reader->readInt8(),
                // machine-order 16-bit integer
                'short' => $reader->readInt16(),
                // machine-order 32-bit integer
                'long' => $reader->readInt32(),
                // arbitrary-length string
                'string' => $reader->readString8(strlen((string) $matchData)),
                // long integer date (seconds since Unix epoch)
                'date' => $reader->readInt64BE(),
                // big-endian 16-bit integer
                'beshort' => $reader->readUInt16BE(),
                // big-endian 32-bit integer date
                'belong', 'bedate' => $reader->readUInt32BE(),
                // little-endian 16-bit integer
                'leshort' => $reader->readUInt16LE(),
                // little-endian 32-bit integer date
                'lelong', 'ledate' => $reader->readUInt32LE(),
                default => null,
            };

            if (strcmp((string) $data, (string) $matchData) == 0) {
                if (isset($chunks['MIMEType']) && ($chunks['MIMEType'] !== '' && $chunks['MIMEType'] !== '0')) {
                    return $chunks['MIMEType'];
                }
                if (isset($chunks['Description']) && ($chunks['Description'] !== '' && $chunks['Description'] !== '0')) {
                    return rtrim($chunks['Description'], "\n");
                }
            }
        }

        return $default;
    }

    /**
     * Returns the results of the mime type check either as a boolean or an
     * array of boolean values.
     * @param string|Array $filename The file path whose type to test.
     * @param string|Array $mimeType The mime type to test against.
     * @return boolean|Array
     */
    public function isMimeType($filename, $mimeType)
    {
        if (is_array($filename)) {
            $result = [];
            foreach ($filename as $key => $value) {
                $result[] = $this->getMimeType($value) ==
                     (is_array($mimeType) ? $mimeType[$key] : $mimeType);
            }

            return $result;
        } else {
            return $this->getMimeType($filename) == $mimeType;
        }
    }
}
