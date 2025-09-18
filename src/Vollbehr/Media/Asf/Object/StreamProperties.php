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
 * The _Stream Properties Object_ defines the specific properties and
 * characteristics of a digital media stream. This object defines how a digital
 * media stream within the _Data Object_ is interpreted, as well as the
 * specific format (of elements) of the _Data Packet_ itself.
 * Whereas every stream in an ASF presentation, including each stream in a
 * mutual exclusion relationship, must be represented by a _Stream Properties
 * Object_, in certain cases, this object might be found embedded in the
 * _Extended Stream Properties Object_.
 * @author Sven Vollbehr
 */
final class StreamProperties extends \Vollbehr\Media\Asf\BaseObject
{
    /**
     * Indicates, if set, that the data contained in this stream is encrypted
     * and will be unreadable unless there is a way to decrypt the stream.
     */
    public const ENCRYPTED_CONTENT = 0x8000;
    public const AUDIO_MEDIA           = 'f8699e40-5b4d-11cf-a8fd-00805f5c442b';
    public const VIDEO_MEDIA           = 'bc19efc0-5b4d-11cf-a8fd-00805f5c442b';
    public const COMMAND_MEDIA         = '59dacfc0-59e6-11d0-a3ac-00a0c90348f6';
    public const JFIF_MEDIA            = 'b61be100-5b4e-11cf-a8fD-00805f5c442b';
    public const DEGRADABLE_JPEG_MEDIA = '35907dE0-e415-11cf-a917-00805f5c442b';
    public const FILE_TRANSFER_MEDIA   = '91bd222c-f21c-497a-8b6d-5aa86bfc0185';
    public const BINARY_MEDIA          = '3afb65e2-47ef-40f2-ac2c-70a90d71d343';

    public const NO_ERROR_CORRECTION = '20fb5700-5b55-11cf-a8fd-00805f5c442b';
    public const AUDIO_SPREAD        = 'bfc3cd50-618f-11cf-8bb2-00aa00b4e220';

    /** @var string */
    private $_streamType;

    /** @var string */
    private $_errorCorrectionType;

    /** @var integer */
    private $_timeOffset;

    /** @var integer */
    private $_flags;

    /** @var integer */
    private $_reserved;

    private array $_typeSpecificData = [];

    private array $_errorCorrectionData = [];

    /**
     * Constructs the class with given parameters and reads object related data
     * from the ASF file.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        parent::__construct($reader, $options);
        $this->_streamType          = $this->_reader->readGuid();
        $this->_errorCorrectionType = $this->_reader->readGuid();
        $this->_timeOffset          = $this->_reader->readInt64LE();
        $typeSpecificDataLength     = $this->_reader->readUInt32LE();
        $errorCorrectionDataLength  = $this->_reader->readUInt32LE();
        $this->_flags               = $this->_reader->readUInt16LE();
        $this->_reserved            = $this->_reader->readUInt32LE();

        switch ($this->_streamType) {
            case self::AUDIO_MEDIA:
                $this->_typeSpecificData = ['codecId' => $this->_reader->readUInt16LE(),
                     'numberOfChannels' => $this->_reader->readUInt16LE(),
                     'samplesPerSecond' => $this->_reader->readUInt32LE(),
                     'avgNumBytesPerSecond' => $this->_reader->readUInt32LE(),
                     'blockAlignment' => $this->_reader->readUInt16LE(),
                     'bitsPerSample' => $this->_reader->readUInt16LE()];
                $codecSpecificDataSize                        = $this->_reader->readUInt16LE();
                $this->_typeSpecificData['codecSpecificData'] = $this->_reader->read($codecSpecificDataSize);
                break;
            case self::VIDEO_MEDIA:
                $this->_typeSpecificData = ['encodedImageWidth' => $this->_reader->readUInt32LE(),
                     'encodedImageHeight' => $this->_reader->readUInt32LE(),
                     'reservedFlags' => $this->_reader->readInt8()];
                $this->_reader->skip(2);
                $formatDataSize          = $this->_reader->readUInt32LE();
                $this->_typeSpecificData = array_merge(
                    $this->_typeSpecificData,
                    ['imageWidth' => $this->_reader->readUInt32LE(),
                      'imageHeight' => $this->_reader->readUInt32LE(),
                      'reserved' => $this->_reader->readUInt16LE(),
                      'bitsPerPixelCount' => $this->_reader->readUInt16LE(),
                      'compressionId' => $this->_reader->readUInt32LE(),
                      'imageSize' => $this->_reader->readUInt32LE(),
                      'horizontalPixelsPerMeter' =>
                      $this->_reader->readUInt32LE(),
                      'verticalPixelsPerMeter' =>
                      $this->_reader->readUInt32LE(),
                      'colorsUsedCount' => $this->_reader->readUInt32LE(),
                      'importantColorsCount' => $this->_reader->readUInt32LE(),
                      'codecSpecificData' =>
                      $this->_reader->read($formatDataSize - 38)]
                );
                break;
            case self::JFIF_MEDIA:
                $this->_typeSpecificData = ['imageWidth' => $this->_reader->readUInt32LE(),
                     'imageHeight' => $this->_reader->readUInt32LE(),
                     'reserved' => $this->_reader->readUInt32LE()];
                break;
            case self::DEGRADABLE_JPEG_MEDIA:
                $this->_typeSpecificData = ['imageWidth' => $this->_reader->readUInt32LE(),
                     'imageHeight' => $this->_reader->readUInt32LE(),
                     $this->_reader->readUInt16LE(),
                     $this->_reader->readUInt16LE(),
                     $this->_reader->readUInt16LE()];
                $interchangeDataSize = $this->_reader->readUInt16LE();
                if ($interchangeDataSize == 0) {
                    $interchangeDataSize++;
                }
                $this->_typeSpecificData['interchangeData'] = $this->_reader->read($interchangeDataSize);
                break;
            case self::FILE_TRANSFER_MEDIA:
                // break intentionally omitted
            case self::BINARY_MEDIA:
                $this->_typeSpecificData = ['majorMediaType' => $this->_reader->getGUID(),
                     'mediaSubtype' => $this->_reader->getGUID(),
                     'fixedSizeSamples' => $this->_reader->readUInt32LE(),
                     'temporalCompression' => $this->_reader->readUInt32LE(),
                     'sampleSize' => $this->_reader->readUInt32LE(),
                     'formatType' => $this->_reader->getGUID()];
                $formatDataSize                        = $this->_reader->readUInt32LE();
                $this->_typeSpecificData['formatData'] = $this->_reader->read($formatDataSize);
                break;
            case self::COMMAND_MEDIA:
                // break intentionally omitted
            default:
                $this->_reader->skip($typeSpecificDataLength);
                break;
        }
        switch ($this->_errorCorrectionType) {
            case self::AUDIO_SPREAD:
                $this->_errorCorrectionData = ['span' => $this->_reader->readInt8(),
                     'virtualPacketLength' => $this->_reader->readUInt16LE(),
                     'virtualChunkLength' => $this->_reader->readUInt16LE()];
                $silenceDataSize                           = $this->_reader->readUInt16LE();
                $this->_errorCorrectionData['silenceData'] = $this->_reader->read($silenceDataSize);
                break;
            case self::NO_ERROR_CORRECTION:
                // break intentionally omitted
            default:
                $this->_reader->skip($errorCorrectionDataLength);
                break;
        }
    }

    /**
     * Returns the number of this stream. 0 is an invalid stream. Valid values
     * are between 1 and 127. The numbers assigned to streams in an ASF
     * presentation may be any combination of unique values; parsing logic must
     * not assume that streams are numbered sequentially.
     */
    public function getStreamNumber(): int
    {
        return $this->_flags & 0x3f;
    }

    /**
     * Returns the number of this stream. 0 is an invalid stream. Valid values
     * are between 1 and 127. The numbers assigned to streams in an ASF
     * presentation may be any combination of unique values; parsing logic must
     * not assume that streams are numbered sequentially.
     * @param integer $streamNumber The number of this stream.
     */
    public function setStreamNumber($streamNumber): void
    {
        if ($streamNumber < 1 || $streamNumber > 127) {
            throw new \Vollbehr\Media\Asf\Exception('Invalid argument');
        }
        $this->_flags = ($this->_flags & 0xffc0) | ($streamNumber & 0x3f);
    }

    /**
     * Returns the type of the stream (for example, audio, video, and so on).
     * @return string
     */
    public function getStreamType()
    {
        return $this->_streamType;
    }

    /**
     * Sets the type of the stream (for example, audio, video, and so on).
     * @param integer $streamType The type of the stream.
     */
    public function setStreamType($streamType): void
    {
        $this->_streamType = $streamType;
    }

    /**
     * Returns the error correction type used by this digital media stream. For
     * streams other than audio, this value should be set to
     * NO_ERROR_CORRECTION. For audio streams, this value should be set to
     * AUDIO_SPREAD.
     * @return string
     */
    public function getErrorCorrectionType()
    {
        return $this->_errorCorrectionType;
    }

    /**
     * Sets the error correction type used by this digital media stream. For
     * streams other than audio, this value should be set to
     * NO_ERROR_CORRECTION. For audio streams, this value should be set to
     * AUDIO_SPREAD.
     * @param integer $errorCorrectionType The error correction type used by
     *        this digital media stream.
     */
    public function setErrorCorrectionType($errorCorrectionType): void
    {
        $this->_errorCorrectionType = $errorCorrectionType;
    }

    /**
     * Returns the presentation time offset of the stream in 100-nanosecond
     * units. The value of this field is added to all of the timestamps of the
     * samples in the stream. This value shall be equal to the send time of the
     * first interleaved packet in the data section. The value of this field is
     * typically 0. It is non-zero in the case when an ASF file is edited and it
     * is not possible for the editor to change the presentation times and send
     * times of ASF packets. Note that if more than one stream is present in an
     * ASF file the offset values of all stream properties objects must be
     * equal.
     * @return integer
     */
    public function getTimeOffset()
    {
        return $this->_timeOffset;
    }

    /**
     * Sets the presentation time offset of the stream in 100-nanosecond units.
     * The value of this field is added to all of the timestamps of the samples
     * in the stream. This value shall be equal to the send time of the first
     * interleaved packet in the data section. The value of this field is
     * typically 0. It is non-zero in the case when an ASF file is edited and it
     * is not possible for the editor to change the presentation times and send
     * times of ASF packets. Note that if more than one stream is present in an
     * ASF file the offset values of all stream properties objects must be
     * equal.
     * @param integer $timeOffset The presentation time offset of the stream.
     */
    public function setTimeOffset($timeOffset): void
    {
        $this->_timeOffset = $timeOffset;
    }

    /**
     * Checks whether or not the flag is set. Returns <var>true</var> if the
     * flag is set, <var>false</var> otherwise.
     * @param integer $flag The flag to query.
     */
    public function hasFlag($flag): bool
    {
        return ($this->_flags & $flag) == $flag;
    }

    /**
     * Returns the flags field.
     * @return integer
     */
    public function getFlags()
    {
        return $this->_flags;
    }

    /**
     * Sets the flags field.
     * @param integer $flags The flags field.
     */
    public function setFlags($flags): void
    {
        $this->_flags = $flags;
    }

    /**
     * Returns type-specific format data. The structure for the _Type-Specific
     * Data_ field is determined by the value stored in the _Stream
     * Type_ field.
     * The type-specific data is returned as key-value pairs of an associate
     * array.
     */
    public function getTypeSpecificData(): array
    {
        return $this->_typeSpecificData;
    }

    /**
     * Sets type-specific format data. The structure for the _Type-Specific
     * Data_ field is determined by the value stored in the _Stream Type_
     * field.
     * @param Array $typeSpecificData The type-specific data as key-value pairs
     *        of an associate array.
     */
    public function setTypeSpecificData(array $typeSpecificData): void
    {
        $this->_typeSpecificData = $typeSpecificData;
    }

    /**
     * Returns data specific to the error correction type. The structure for the
     * _Error Correction Data_ field is determined by the value stored in
     * the _Error Correction Type_ field. For example, an audio data stream
     * might need to know how codec chunks were redistributed, or it might need
     * a sample of encoded silence.
     * The error correction type-specific data is returned as key-value pairs of
     * an associate array.
     * @return integer
     */
    public function getErrorCorrectionData(): array
    {
        return $this->_errorCorrectionData;
    }

    /**
     * Sets data specific to the error correction type. The structure for the
     * _Error Correction Data_ field is determined by the value stored in
     * the _Error Correction Type_ field. For example, an audio data stream
     * might need to know how codec chunks were redistributed, or it might need
     * a sample of encoded silence.
     * @param Array $errorCorrectionData The error correction type-specific data
     *        as key-value pairs of an associate array.
     */
    public function setErrorCorrectionData(array $errorCorrectionData): void
    {
        $this->_errorCorrectionData = $errorCorrectionData;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        $typeSpecificData = new \Vollbehr\Io\StringWriter();
        switch ($this->_streamType) {
            case self::AUDIO_MEDIA:
                $typeSpecificData
                    ->writeUInt16LE($this->_typeSpecificData['codecId'])
                    ->writeUInt16LE($this->_typeSpecificData['numberOfChannels'])
                    ->writeUInt32LE($this->_typeSpecificData['samplesPerSecond'])
                    ->writeUInt32LE($this->_typeSpecificData['avgNumBytesPerSecond'])
                    ->writeUInt16LE($this->_typeSpecificData['blockAlignment'])
                    ->writeUInt16LE($this->_typeSpecificData['bitsPerSample'])
                    ->writeUInt16LE(strlen((string) $this->_typeSpecificData['codecSpecificData']))
                    ->write($this->_typeSpecificData['codecSpecificData']);
                break;
            case self::VIDEO_MEDIA:
                $typeSpecificData
                    ->writeUInt32LE($this->_typeSpecificData['encodedImageWidth'])
                    ->writeUInt32LE($this->_typeSpecificData['encodedImageHeight'])
                    ->writeInt8($this->_typeSpecificData['reservedFlags'])
                    ->writeUInt16LE(0) // Reserved
                    ->writeUInt32LE(38 +
                         strlen((string) $this->_typeSpecificData['codecSpecificData']))
                    ->writeUInt32LE($this->_typeSpecificData['imageWidth'])
                    ->writeUInt32LE($this->_typeSpecificData['imageHeight'])
                    ->writeUInt16LE($this->_typeSpecificData['reserved'])
                    ->writeUInt16LE($this->_typeSpecificData['bitsPerPixelCount'])
                    ->writeUInt32LE($this->_typeSpecificData['compressionId'])
                    ->writeUInt32LE($this->_typeSpecificData['imageSize'])
                    ->writeUInt32LE($this->_typeSpecificData['horizontalPixelsPerMeter'])
                    ->writeUInt32LE($this->_typeSpecificData['verticalPixelsPerMeter'])
                    ->writeUInt32LE($this->_typeSpecificData['colorsUsedCount'])
                    ->writeUInt32LE($this->_typeSpecificData['importantColorsCount'])
                    ->write($this->_typeSpecificData['codecSpecificData']);
                break;
            case self::JFIF_MEDIA:
                $typeSpecificData
                    ->writeUInt32LE($this->_typeSpecificData['imageWidth'])
                    ->writeUInt32LE($this->_typeSpecificData['imageHeight'])
                    ->writeUInt32LE(0);
                break;
            case self::DEGRADABLE_JPEG_MEDIA:
                $typeSpecificData
                    ->writeUInt32LE($this->_typeSpecificData['imageWidth'])
                    ->writeUInt32LE($this->_typeSpecificData['imageHeight'])
                    ->writeUInt16LE(0)
                    ->writeUInt16LE(0)
                    ->writeUInt16LE(0);
                $interchangeDataSize = strlen((string) $this->_typeSpecificData['interchangeData']);
                if ($interchangeDataSize == 1) {
                    $interchangeDataSize = 0;
                }
                $typeSpecificData
                    ->writeUInt16LE($interchangeDataSize)
                    ->write($this->_typeSpecificData['interchangeData']);
                break;
            case self::FILE_TRANSFER_MEDIA:
                // break intentionally omitted
            case self::BINARY_MEDIA:
                $typeSpecificData
                    ->writeGuid($this->_typeSpecificData['majorMediaType'])
                    ->writeGuid($this->_typeSpecificData['mediaSubtype'])
                    ->writeUInt32LE($this->_typeSpecificData['fixedSizeSamples'])
                    ->writeUInt32LE($this->_typeSpecificData['temporalCompression'])
                    ->writeUInt32LE($this->_typeSpecificData['sampleSize'])
                    ->writeGuid($this->_typeSpecificData['formatType'])
                    ->writeUInt32LE(strlen((string) $this->_typeSpecificData['formatData']))
                    ->write($this->_typeSpecificData['formatData']);
                break;
            case self::COMMAND_MEDIA:
                // break intentionally omitted
            default:
                break;
        }

        $errorCorrectionData = new \Vollbehr\Io\StringWriter();
        switch ($this->_errorCorrectionType) {
            case self::AUDIO_SPREAD:
                $errorCorrectionData
                    ->writeInt8($this->_errorCorrectionData['span'])
                    ->writeUInt16LE($this->_errorCorrectionData['virtualPacketLength'])
                    ->writeUInt16LE($this->_errorCorrectionData['virtualChunkLength'])
                    ->writeUInt16LE(strlen((string) $this->_errorCorrectionData['silenceData']))
                    ->write($this->_errorCorrectionData['silenceData']);
                break;
            case self::NO_ERROR_CORRECTION:
                // break intentionally omitted
            default:
                break;
        }

        $this->setSize(24 /* for header */ + 54 + $typeSpecificData->getSize() +
             $errorCorrectionData->getSize());

        $writer->writeGuid($this->getIdentifier())
               ->writeInt64LE($this->getSize())
               ->writeGuid($this->_streamType)
               ->writeGuid($this->_errorCorrectionType)
               ->writeInt64LE($this->_timeOffset)
               ->writeUInt32LE($typeSpecificData->getSize())
               ->writeUInt32LE($errorCorrectionData->getSize())
               ->writeUInt16LE($this->_flags)
               ->writeUInt32LE($this->_reserved)
               ->write($typeSpecificData->toString())
               ->write($errorCorrectionData->toString());
    }
}
