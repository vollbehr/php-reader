<?php

declare(strict_types=1);

namespace Vollbehr\Media\Asf;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * The base unit of organization for ASF files is called the ASF object. It
 * consists of a 128-bit GUID for the object, a 64-bit integer object size, and
 * the variable-length object data.
 * @author Sven Vollbehr
 */
abstract class BaseObject
{
    /* ASF Objects */
    public const HEADER             = '75b22630-668e-11cf-a6d9-00aa0062ce6c';
    public const DATA               = '75b22636-668e-11cf-a6d9-00aa0062ce6c';
    public const SIMPLE_INDEX       = '33000890-e5b1-11cf-89f4-00a0c90349cb';
    public const INDEX              = 'd6e229d3-35da-11d1-9034-00a0c90349be';
    public const MEDIA_OBJECT_INDEX = 'feb103f8-12ad-4c64-840f-2a1d2f7ad48c';
    public const TIMECODE_INDEX     = '3cb73fd0-0c4a-4803-953d-edf7b6228f0c';

    /* Header Objects */
    public const FILE_PROPERTIES              = '8cabdca1-a947-11cf-8ee4-00c00c205365';
    public const STREAM_PROPERTIES            = 'b7dc0791-a9b7-11cf-8ee6-00c00c205365';
    public const HEADER_EXTENSION             = '5fbf03b5-a92e-11cf-8ee3-00c00c205365';
    public const CODEC_LIST                   = '86d15240-311d-11d0-a3a4-00a0c90348f6';
    public const SCRIPT_COMMAND               = '1efb1a30-0b62-11d0-a39b-00a0c90348f6';
    public const MARKER                       = 'f487cd01-a951-11cf-8ee6-00c00c205365';
    public const BITRATE_MUTUAL_EXCLUSION     = 'd6e229dc-35da-11d1-9034-00a0c90349be';
    public const ERROR_CORRECTION             = '75b22635-668e-11cf-a6d9-00aa0062ce6c';
    public const CONTENT_DESCRIPTION          = '75b22633-668e-11cf-a6d9-00aa0062ce6c';
    public const EXTENDED_CONTENT_DESCRIPTION = 'd2d0a440-e307-11d2-97f0-00a0c95ea850';
    public const CONTENT_BRANDING             = '2211b3fa-bd23-11d2-b4b7-00a0c955fc6e';
    public const STREAM_BITRATE_PROPERTIES    = '7bf875ce-468d-11d1-8d82-006097c9a2b2';
    public const CONTENT_ENCRYPTION           = '2211b3fb-bd23-11d2-b4b7-00a0c955fc6e';
    public const EXTENDED_CONTENT_ENCRYPTION  = '298ae614-2622-4c17-b935-dae07ee9289c';
    public const DIGITAL_SIGNATURE            = '2211b3fc-bd23-11d2-b4b7-00a0c955fc6e';
    public const PADDING                      = '1806d474-cadf-4509-a4ba-9aabcb96aae8';

    /* Header Extension Objects */
    public const EXTENDED_STREAM_PROPERTIES    = '14e6a5cb-c672-4332-8399-a96952065b5a';
    public const ADVANCED_MUTUAL_EXCLUSION     = 'a08649cf-4775-4670-8a16-6e35357566cd';
    public const GROUP_MUTUAL_EXCLUSION        = 'd1465a40-5a79-4338-b71b-e36b8fd6c249';
    public const STREAM_PRIORITIZATION         = 'd4fed15b-88d3-454f-81f0-ed5c45999e24';
    public const BANDWIDTH_SHARING             = 'a69609e6-517b-11d2-b6af-00c04fd908e9';
    public const LANGUAGE_LIST                 = '7c4346a9-efe0-4bfc-b229-393ede415c85';
    public const METADATA                      = 'c5f8cbea-5baf-4877-8467-aa8c44fa4cca';
    public const METADATA_LIBRARY              = '44231c94-9498-49d1-a141-1d134e457054';
    public const INDEX_PARAMETERS              = 'd6e229df-35da-11d1-9034-00a0c90349be';
    public const MEDIA_OBJECT_INDEX_PARAMETERS = '6b203bad-3f11-48e4-aca8-d7613de2cfa7';
    public const TIMECODE_INDEX_PARAMETERS     = 'f55e496d-9797-4b5d-8c8b-604dfe9bfb24';
    public const COMPATIBILITY                 = '75b22630-668e-11cf-a6d9-00aa0062ce6c';
    public const ADVANCED_CONTENT_ENCRYPTION   = '43058533-6981-49e6-9b74-ad12cb86d58c';

    /**
     * The options array.
     * @var Array
     */
    protected $_options;
    /** @var integer */
    private $_offset = false;

    /** @var string */
    private $_identifier = false;

    /** @var integer */
    private $_size = false;

    /** @var BaseObject */
    private $_parent;

    /**
     * Constructs the class with given parameters and options.
     * @param \Vollbehr\Io\Reader $_reader The reader object.
     * @param Array          $options The options array.
     */
    public function __construct(/** The reader object. */
        protected $_reader,
        &$options = []
    ) {
        $this->_options = &$options;

        if (($this->_reader = $this->_reader) === null) {
            if (defined($constant = 'self::' . strtoupper((string) preg_replace('/(?<=[a-z])[A-Z]/', '_$0', substr(static::class, 22))))) {
                $this->_identifier = constant($constant);
            } else {

                throw new Exception('Object identifier could not be determined');
            }
        } else {
            $this->_offset     = $this->_reader->getOffset();
            $this->_identifier = $this->_reader->readGuid();
            $this->_size       = $this->_reader->readInt64LE();
        }
    }

    /**
     * Returns the options array.
     * @return Array
     */
    final public function &getOptions()
    {
        return $this->_options;
    }

    /**
     * Returns the given option value, or the default value if the option is not
     * defined.
     * @param string $option The name of the option.
     * @param mixed $defaultValue The default value to be returned.
     */
    final public function getOption($option, $defaultValue = null)
    {
        return $this->_options[$option] ?? $defaultValue;
    }

    /**
     * Sets the options array. See {@see \Vollbehr\Media\Asf} class for available
     * options.
     * @param Array $options The options array.
     */
    final public function setOptions(&$options): void
    {
        $this->_options = &$options;
    }

    /**
     * Sets the given option the given value.
     * @param string $option The name of the option.
     * @param mixed $value The value to set for the option.
     */
    final public function setOption($option, $value): void
    {
        $this->_options[$option] = $value;
    }

    /**
     * Clears the given option value.
     * @param string $option The name of the option.
     */
    final public function clearOption($option): void
    {
        unset($this->_options[$option]);
    }

    /**
     * Returns the file offset to object start, or <var>false</var> if the
     * object was created on heap.
     * @return integer
     */
    final public function getOffset()
    {
        return $this->_offset;
    }

    /**
     * Sets the file offset where the object starts.
     * @param integer $offset The file offset to object start.
     */
    final public function setOffset($offset): void
    {
        $this->_offset = $offset;
    }

    /**
     * Returns the GUID of the ASF object.
     * @return string
     */
    final public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Set the GUID of the ASF object.
     * @param string $identifier The GUID
     */
    final public function setIdentifier($identifier): void
    {
        $this->_identifier = $identifier;
    }

    /**
     * Returns the object size in bytes, including the header.
     * @return integer
     */
    final public function getSize()
    {
        return $this->_size;
    }

    /**
     * Sets the object size. The size must include the 24 byte header.
     * @param integer $size The object size.
     */
    final public function setSize($size): void
    {
        if ($this->_parent !== null) {
            $this->_parent->setSize((max($this->_parent->getSize(), 0)) +
                 $size - (max($this->_size, 0)));
        }
        $this->_size = $size;
    }

    /**
     * Returns the parent object containing this object.
     * @return BaseObject
     */
    final public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Sets the parent containing object.
     * @param BaseObject $parent The parent object.
     */
    final public function setParent(&$parent): void
    {
        $this->_parent = $parent;
    }

    /**
     * Writes the object data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     * @return void
     */
    abstract public function write($writer);
    /**
     * Magic function so that $obj->value will work. The method will attempt to
     * invoke a getter method. If there are no getter methods with given name,
     * an exception is thrown.
     * @param string $name The field name.
     */
    public function __get(string $name)
    {
        if (method_exists($this, 'get' . ucfirst($name))) {
            return call_user_func([$this, 'get' . ucfirst($name)]);
        }
        if (method_exists($this, 'is' . ucfirst($name))) {
            return call_user_func([$this, 'is' . ucfirst($name)]);
        }

        throw new Exception('Unknown field: ' . $name);
    }

    /**
     * Magic function so that assignments with $obj->value will work.
     * @param string $name  The field name.
     * @param string $value The field value.
     */
    public function __set(string $name, $value)
    {
        if (method_exists($this, 'set' . ucfirst($name))) {
            call_user_func([$this, 'set' . ucfirst($name)], $value);
        } else {

            throw new Exception('Unknown field: ' . $name);
        }
    }
}
