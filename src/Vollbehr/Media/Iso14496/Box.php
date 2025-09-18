<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**
 * A base class for all ISO 14496-12 boxes.
 * @author Sven Vollbehr
 */
class Box
{
    /**
     * The reader object.
     * @var Reader
     */
    protected $_reader;
    /** @var Array */
    private $_options;
    /** @var integer */
    private $_offset = false;

    /** @var integer */
    private $_size = false;

    /** @var string */
    private $_type;
    /** @var Box */
    private $_parent;

    /** @var boolean */
    private $_container = false;

    private array $_boxes = [];

    private static array $_path = [];

    /**
     * Constructs the class with given parameters and options.
     * @param \Vollbehr\Io\Reader $reader  The reader object.
     * @param Array          $options The options array.
     */
    public function __construct($reader, &$options = [])
    {
        if (($this->_reader = $reader) === null) {
            $this->_type = strtolower(substr(static::class, -4));
        } else {
            $this->_offset = $this->_reader->getOffset();
            $this->_size   = $this->_reader->readUInt32BE();
            $this->_type   = $this->_reader->read(4);

            if ($this->_size == 1) {
                $this->_size = $this->_reader->readInt64BE();
            }
            if ($this->_size == 0) {
                $this->_size = $this->_reader->getSize() - $this->_offset;
            }
            if ($this->_type == 'uuid') {
                $this->_type = $this->_reader->readGUID();
            }
        }
        $this->_options = &$options;
    }

    /**
     * Releases any references to contained boxes and the parent.
     */
    public function __destruct()
    {
        unset($this->_boxes);
        unset($this->_parent);
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
     * Sets the options array. See {@see \Vollbehr\Media\Id3v2} class for available
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
     * Returns the file offset to box start, or <var>false</var> if the box was
     * created on heap.
     * @return integer
     */
    final public function getOffset()
    {
        return $this->_offset;
    }
    /**
     * Sets the file offset where the box starts.
     * @param integer $offset The file offset to box start.
     */
    final public function setOffset($offset): void
    {
        $this->_offset = $offset;
    }
    /**
     * Returns the box size in bytes read from the file, including the size and
     * type header, fields, and all contained boxes, or <var>false</var> if the
     * box was created on heap.
     * @return integer
     */
    final public function getSize()
    {
        return $this->_size;
    }
    /**
     * Sets the box size. The size must include the size and type header,
     * fields, and all contained boxes.
     * The method will propagate size change to box parents.
     * @param integer $size The box size.
     */
    final protected function setSize(int | float $size)
    {
        if ($this->_parent !== null) {
            $this->_parent->setSize((max($this->_parent->getSize(), 0)) +
                 $size - (max($this->_size, 0)));
        }
        $this->_size = $size;
    }
    /**
     * Returns the box type.
    *
     * @return string
     */
    final public function getType()
    {
        return $this->_type;
    }
    /**
     * Sets the box type.
     * @param string $type The box type.
     */
    final public function setType($type): void
    {
        $this->_type = $type;
    }
    /**
     * Returns the parent box containing this box.
     * @return Box
     */
    final public function getParent()
    {
        return $this->_parent;
    }
    /**
     * Sets the parent containing box.
     * @param Box $parent The parent box.
     */
    public function setParent(&$parent): void
    {
        $this->_parent = $parent;
    }
    /**
     * Returns a boolean value corresponding to whether the box is a container.
     * @return boolean
     */
    final public function isContainer()
    {
        return $this->_container;
    }
    /**
     * Returns a boolean value corresponding to whether the box is a container.
     * @return boolean
     */
    final public function getContainer()
    {
        return $this->_container;
    }
    /**
     * Sets whether the box is a container.
     * @param boolean $container Whether the box is a container.
     */
    final protected function setContainer($container)
    {
        $this->_container = $container;
    }
    /**
     * Reads and constructs the boxes found within this box.
     * @todo Does not parse iTunes internal ---- boxes.
     */
    final protected function constructBoxes($defaultclassname = Box::class)
    {
        $base = $this->getOption('base', '');
        if ($this->getType() != 'file') {
            self::$_path[] = $this->getType();
        }
        $path = implode('.', self::$_path);

        while (true) {
            $offset = $this->_reader->getOffset();
            if ($offset >= $this->_offset + $this->_size) {
                break;
            }
            $size = $this->_reader->readUInt32BE();
            $type = rtrim((string) $this->_reader->read(4), ' ');
            if ($size == 1) {
                $size = $this->_reader->readInt64BE();
            }
            if ($size == 0) {
                $size = $this->_reader->getSize() - $offset;
            }

            if (preg_match("/^\xa9?[a-z0-9]{3,4}$/i", $type) &&
                substr((string) $base, 0, min(strlen((string) $base), strlen($tmp = $path . ($path !== '' && $path !== '0' ? '.' : '') . $type))) === substr($tmp, 0, min(strlen((string) $base), strlen($tmp)))) {
                $this->_reader->setOffset($offset);
                $classname = sprintf('\\Vollbehr\\Media\\Iso14496\\Box\\%s', ucfirst($type));
                if (class_exists($classname)) {
                    $box = new $classname($this->_reader, $this->_options);
                } else {
                    $box = new $defaultclassname($this->_reader, $this->_options);
                }
                $box->setParent($this);
                if (!isset($this->_boxes[$box->getType()])) {
                    $this->_boxes[$box->getType()] = [];
                }
                $this->_boxes[$box->getType()][] = $box;
            }
            $this->_reader->setOffset($offset + $size);
        }

        array_pop(self::$_path);
    }

    /**
     * Checks whether the box given as an argument is present in the file. Returns
     * <var>true</var> if one or more boxes are present, <var>false</var>
     * otherwise.
     * @param string $identifier The box identifier.
     * @throws Exception if called on a non-container box
     */
    final public function hasBox($identifier): bool
    {
        if (!$this->isContainer()) {
            throw new Exception('Box not a container');
        }

        return isset($this->_boxes[$identifier]);
    }
    /**
     * Returns all the boxes the file contains as an associate array. The box
     * identifiers work as keys having an array of boxes as associated value.
    *
     * @throws Exception if called on a non-container box
     */
    final public function getBoxes(): array
    {
        if (!$this->isContainer()) {
            throw new Exception('Box not a container');
        }

        return $this->_boxes;
    }

    /**
     * Returns an array of boxes matching the given identifier or an empty array
     * if no boxes matched the identifier.
    *
     * The identifier may contain wildcard characters '*' and '?'. The asterisk
     * matches against zero or more characters, and the question mark matches
     * any single character.
     * Please note that one may also use the shorthand $obj->identifier to
     * access the first box with the identifier given. Wildcards cannot be used
     * with the shorthand and they will not work with user defined uuid types.
     * @param string $identifier The box identifier.
     * @throws Exception if called on a non-container box
     */
    final public function getBoxesByIdentifier($identifier): array
    {
        if (!$this->isContainer()) {
            throw new Exception('Box not a container');
        }
        $matches       = [];
        $searchPattern = '/^' .
            str_replace(['*', '?'], ['.*', '.'], $identifier) . '$/i';
        foreach ($this->_boxes as $identifier => $boxes) {
            if (preg_match($searchPattern, (string) $identifier)) {
                foreach ($boxes as $box) {
                    $matches[] = $box;
                }
            }
        }

        return $matches;
    }

    /**
     * Removes any boxes matching the given box identifier.
     * The identifier may contain wildcard characters '*' and '?'. The asterisk
     * matches against zero or more characters, and the question mark matches any
     * single character.
     * One may also use the shorthand unset($obj->identifier) to achieve the same
     * result. Wildcards cannot be used with the shorthand method.
     * @param string $identifier The box identifier.
     * @throws Exception if called on a non-container box
     */
    final public function removeBoxesByIdentifier($identifier): void
    {
        if (!$this->isContainer()) {
            throw new Exception('Box not a container');
        }
        $searchPattern = '/^' .
            str_replace(['*', '?'], ['.*', '.'], $identifier) . '$/i';
        foreach ($this->_objects as $identifier => $objects) {
            if (preg_match($searchPattern, $identifier)) {
                unset($this->_objects[$identifier]);
            }
        }
    }

    /**
     * Adds a new box into the current box and returns it.
     * @param Box $box The box to add
     * @throws Exception if called on a non-container box
     * @return Box
     */
    final public function addBox(&$box)
    {
        if (!$this->isContainer()) {
            throw new Exception('Box not a container');
        }
        $box->setParent($this);
        $box->setOptions($this->_options);
        if (!$this->hasBox($box->getType())) {
            $this->_boxes[$box->getType()] = [];
        }
        return $this->_boxes[$box->getType()][] = $box;
    }

    /**
     * Removes the given box.
     * @param Box $box The box to remove
     * @throws Exception if called on a non-container box
     */
    final public function removeBox($box): void
    {
        if (!$this->isContainer()) {
            throw new Exception('Box not a container');
        }
        if ($this->hasBox($box->getType())) {
            foreach ($this->_boxes[$box->getType()] as $key => $value) {
                if ($box === $value) {
                    unset($this->_boxes[$box->getType()][$key]);
                }
            }
        }
    }

    /**
     * Returns the number of boxes this box contains.
     */
    final public function getBoxCount(): int
    {
        if (!$this->isContainer()) {
            return 0;
        }

        return count($this->_boxes);
    }

    /**
     * Magic function so that $obj->value will work. If called on a container box,
     * the method will first attempt to return the first contained box that
     * matches the identifier, and if not found, invoke a getter method.
     * If there are no boxes or getter methods with given name, the method
     * attempts to create a frame with given identifier.
     * If none of these work, an exception is thrown.
     * @param string $name The box or field name.
     */
    public function __get(string $name)
    {
        if ($this->isContainer() &&
                isset($this->_boxes[str_pad($name, 4, ' ')])) {
            return $this->_boxes[str_pad($name, 4, ' ')][0];
        }
        if (method_exists($this, 'get' . ucfirst($name))) {
            return call_user_func([$this, 'get' . ucfirst($name)]);
        }
        $classname = sprintf('\\Vollbehr\\Media\\Iso14496\\Box\\%s', ucfirst($name));
        if (class_exists($classname)) {
            return $this->addBox(new $classname());
        }
        throw new Exception('Unknown box/field: ' . $name);
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

    /**
     * Magic function so that isset($obj->value) will work. This method checks
     * whether the box is a container and contains a box that matches the
     * identifier.
     * @param string $name The box name.
     * @return boolean
     */
    public function __isset($name)
    {
        return ($this->isContainer() && isset($this->_boxes[$name]));
    }
    /**
     * Magic function so that unset($obj->value) will work. This method removes
     * all the boxes from this container that match the identifier.
     * @param string $name The box name.
     */
    public function __unset($name)
    {
        if ($this->isContainer()) {
            unset($this->_boxes[$name]);
        }
    }

    /**
     * Returns the box heap size in bytes, including the size and
     * type header, fields, and all contained boxes. The box size is updated to
     * reflect that of the heap size upon write. Subclasses should overwrite
     * this method and call the parent method to get the calculated header and
     * subbox sizes and then add their own bytes to that.
     * @return integer
     */
    public function getHeapSize(): float | int
    {
        $size = 8;
        if ($this->isContainer()) {
            foreach ($this->getBoxes() as $boxes) {
                foreach ($boxes as $box) {
                    $size += $box->getHeapSize();
                }
            }
        }
        if ($size > 0xffffffff) {
            $size += 8;
        }
        if (strlen($this->_type) > 4) {
            $size += 16;
        }

        return $size;
    }

    /**
     * Writes the box header. Subclasses should overwrite this method and call
     * the parent method first and then write the box related data.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     * @return void
     */
    protected function _writeData($writer)
    {
        if (static::class === Box::class) {
            throw new Exception('Unknown box \'' . $this->getType() . '\' cannot be written.');
        }

        $this->_size = $this->getHeapSize();
        if ($this->_size > 0xffffffff) {
            $writer->writeUInt32BE(1);
        } else {
            $writer->writeUInt32BE($this->_size);
        }
        if (strlen($this->_type) > 4) {
            $writer->write('uuid');
        } else {
            $writer->write($this->_type);
        }
        if ($this->_size > 0xffffffff) {
            $writer->writeInt64BE($this->_size);
        }
        if (strlen($this->_type) > 4) {
            $writer->writeGuid($this->_type);
        }
    }

    /**
     * Writes the frame data with the header.
     * @param \Vollbehr\Io\Writer $writer The writer object.
     */
    public function write($writer): void
    {
        if (static::class === Box::class) {
            throw new Exception('Unknown box \'' . $this->getType() . '\' cannot be written.');
        }

        $this->_writeData($writer);
        if ($this->isContainer()) {
            foreach ($this->getBoxes() as $boxes) {
                foreach ($boxes as $box) {
                    $box->write($writer);
                }
            }
        }
    }
}
