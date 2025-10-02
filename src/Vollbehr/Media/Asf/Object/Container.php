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
 * An abstract base container class that contains other ASF objects.
 * @author Sven Vollbehr
 */
abstract class Container extends \Vollbehr\Media\Asf\BaseObject
{
    private array $_objects = [];

    /**
     * Reads and constructs the objects found within this object.
     */
    final protected function constructObjects(array $defaultclassnames = [])
    {
        while (true) {
            $offset = $this->_reader->getOffset();
            if ($offset >= $this->getOffset() + $this->getSize()) {
                break;
            }
            $guid = $this->_reader->readGuid();
            $size = $this->_reader->readInt64LE();

            $this->_reader->setOffset($offset);
            if (isset($defaultclassnames[$guid])) {
                $classname = sprintf('\\Vollbehr\\Media\\Asf\\Object\\%s', $defaultclassnames[$guid]);
                if (class_exists($classname)) {
                    $object = new $classname($this->_reader, $this->_options);
                } else {
                    $object = new Unknown($this->_reader, $this->_options);
                }
            } else {

                $object = new Unknown($this->_reader, $this->_options);
            }
            $object->setParent($this);
            if (!$this->hasObject($object->getIdentifier())) {
                $this->_objects[$object->getIdentifier()] = [];
            }
            $this->_objects[$object->getIdentifier()][] = $object;
            $this->_reader->setOffset($offset + $size);
        }
    }

    /**
     * Checks whether the object with given identifier is present in the file.
     * The identifier can either be the object GUID, or name of the constant
     * containing the GUID, or the name of the object class.
     * Returns <var>true</var> if one or more objects are present,
     * <var>false</var> otherwise.
     * @param string $identifier The object GUID, name of the GUID constant, or
     *        object class name.
     * @return boolean
     */
    final public function hasObject($identifier)
    {
        if (defined($constname = static::class . '::' . strtoupper((string) preg_replace('/[A-Z]/', '_$0', $identifier)))) {
            $objects = $this->getObjectsByIdentifier(constant($constname));

            return isset($objects[0]);
        } else {
            return isset($this->_objects[$identifier]);
        }
    }

    /**
     * Returns all the objects the file contains as an associate array. The
     * object identifiers work as keys having an array of ASF objects as
     * associated value.
     * @return Array
     */
    final public function getObjects()
    {
        return $this->_objects;
    }

    /**
     * Returns an array of objects matching the given object GUID or an empty
     * array if no object matched the identifier.
     * The identifier may contain wildcard characters '*' and '?'. The asterisk
     * matches against zero or more characters, and the question mark matches
     * any single character.
     * @param string $identifier The object GUID.
     * @return Array
     */
    final public function getObjectsByIdentifier($identifier)
    {
        $matches       = [];
        $searchPattern = '/^' .
            str_replace(['*', '?'], ['.*', '.'], $identifier) . '$/i';
        foreach ($this->_objects as $identifier => $objects) {
            if (preg_match($searchPattern, (string) $identifier)) {
                foreach ($objects as $object) {
                    $matches[] = $object;
                }
            }
        }

        return $matches;
    }

    /**
     * Returns an array of objects matching the given object constant name or an
     * empty array if no object matched the name.
     * The object constant name can be given in three forms; either using the
     * full name of the constant, the name of the class or the shorthand style
     * of the class name having its first letter in lower case.
     * One may use the shorthand $obj->name to access the first box with the
     * name given directly. Shorthands will not work with user defined uuid
     * types.
     * The name may not contain wildcard characters.
     * @param string $name The object constant name or class name.
     * @return Array
     */
    final public function getObjectsByName(string $name)
    {
        if (defined($constname = static::class . '::' . $name) ||
            defined($constname = static::class . '::' . strtoupper((string) preg_replace('/^_/', '', (string) preg_replace('/[A-Z]/', '_$0', $name))))) {
            return $this->getObjectsByIdentifier(constant($constname));
        }

        return [];
    }

    /**
     * Removes any objects matching the given object GUID.
     * The identifier may contain wildcard characters '*' and '?'. The asterisk
     * matches against zero or more characters, and the question mark matches
     * any single character.
     * One may also use the shorthand unset($obj->name) to achieve the same
     * result. Wildcards cannot be used with the shorthand method.
     * @param string $identifier The object GUID.
     */
    final public function removeObjectsByIdentifier($identifier): void
    {
        $searchPattern = '/^' .
            str_replace(['*', '?'], ['.*', '.'], $identifier) . '$/i';
        foreach (array_keys($this->_objects) as $identifier) {
            if (preg_match($searchPattern, (string) $identifier)) {
                unset($this->_objects[$identifier]);
            }
        }
    }

    /**
     * Removes any objects matching the given object name.
     * The name can be given in three forms; either using the full name of the
     * constant, the name of the class or the shorthand style of the class name
     * having its first letter in lower case.
     * One may also use the shorthand unset($obj->name) to achieve the same
     * result.
     * The name may not contain wildcard characters.
     * @param string $name The object constant name or class name.
     */
    final public function removeObjectsByName($name): void
    {
        if (defined($constname = static::class . '::' . strtoupper((string) preg_replace('/[A-Z]/', '_$0', $name)))) {
            unset($this->_objects[constant($constname)]);
        }
    }

    /**
     * Adds a new object into the current object and returns it.
     * @param \Vollbehr\Media\Asf\BaseObject $object The object to add
     * @return \Vollbehr\Media\Asf\BaseObject
     */
    final public function addObject($object)
    {
        $object->setParent($this);
        $object->setOptions($this->_options);
        if (!$this->hasObject($object->getIdentifier())) {
            $this->_objects[$object->getIdentifier()] = [];
        }

        return $this->_objects[$object->getIdentifier()][] = $object;
    }

    /**
     * Removes the object.
     * @param \Vollbehr\Media\Asf\BaseObject $object The object to remove
     */
    final public function removeObject($object): void
    {
        if ($this->hasObject($object->getIdentifier())) {
            foreach ($this->_objects
                        [$object->getIdentifier()] as $key => $value) {
                if ($object === $value) {
                    unset($this->_objects[$object->getIdentifier()][$key]);
                }
            }
        }
    }

    /**
     * Returns the number of objects this container has.
     * @return integer
     */
    final public function getObjectCount()
    {
        return count($this->_objects);
    }

    /**
     * Override magic function so that $obj->value will work as expected.
     * The method first attempts to call the appropriate getter method. If no
     * field with given name is found, the method attempts to return the right
     * object instead. In other words, calling $obj->value will attempt to
     * return the first object returned by
     * $this->getObjectsByIdentifier(self::value). If no object is found by the
     * given value, a respective class name is tried to instantiate and add to
     * the container.
     * @param string $name The field or object name.
     */
    public function __get($name)
    {
        if (method_exists($this, 'get' . ucfirst($name))) {
            return call_user_func([$this, 'get' . ucfirst($name)]);
        }
        if (method_exists($this, 'is' . ucfirst($name))) {
            return call_user_func([$this, 'is' . ucfirst($name)]);
        }
        if (defined($constname = static::class . '::' . strtoupper((string) preg_replace('/[A-Z]/', '_$0', $name)))) {
            $objects = $this->getObjectsByIdentifier(constant($constname));
            if (isset($objects[0])) {
                return $objects[0];
            } else {
                $classname = sprintf('\\Vollbehr\\Media\\Asf\\Object\\%s', ucfirst($name));
                if (class_exists($classname)) {
                    $obj = new $classname();
                    $obj->setIdentifier(constant($constname));

                    return $this->addObject($obj);
                }
            }
        }

        throw new \Vollbehr\Media\Asf\Exception('Unknown field/object: ' . $name);
    }

    /**
     * Override magic function so that $obj->value will work as expected.
     * The method first attempts to call the appropriate setter method. If no
     * field with given name is found, the method attempts to set the right
     * object instead. In other words, assigning to $obj->value will attempt to
     * set the object with given value's identifier.
     * Please note that using this method will override any prior objects having
     * the same object identifier.
     * @param string $name  The field or object name.
     * @param string $value The field value or object.
     */
    public function __set($name, $value)
    {
        if (method_exists($this, 'set' . ucfirst($name))) {
            call_user_func([$this, 'set' . ucfirst($name)], $value);
        }
        if (defined($constname = static::class . '::' . strtoupper((string) preg_replace('/[A-Z]/', '_$0', $name)))) {
            $value->setOptions($this->_options);
            $this->_objects[constant($constname)] = [$value];
        } else {

            throw new \Vollbehr\Media\Asf\Exception('Unknown field/object: ' . $name);
        }
    }

    /**
     * Magic function so that isset($obj->value) will work. This method checks
     * whether the object by given identifier or name is contained by this
     * container.
     * @param string $name The object identifier or logical name.
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->hasObject($name);
    }

    /**
     * Magic function so that unset($obj->value) will work. This method removes
     * all the objects with the given identifier or name.
     * @param string $name The object identifier or logical name.
     */
    public function __unset($name)
    {
        $this->removeObjectsByName($name);
    }
}
