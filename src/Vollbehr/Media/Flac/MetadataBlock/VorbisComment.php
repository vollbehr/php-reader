<?php

declare(strict_types=1);

namespace Vollbehr\Media\Flac\MetadataBlock;

/**
 * PHP Reader
 * @package   \Vollbehr\Media
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */

/**#@+ @ignore */


/**#@-*/

/**
 * This class represents the vorbis comments metadata block. This block is for storing a list of human-readable
 * name/value pairs. This is the only officially supported tagging mechanism in FLAC. There may be only one
 * VORBIS_COMMENT block in a stream. In some external documentation, Vorbis comments are called FLAC tags to lessen
 * confusion.
 * This class parses the vorbis comments using the {@see \Vollbehr\Media\Vorbis\Header\Comment} class. Any of its method
 * or fields can be used in the context of this class.
 * @author Sven Vollbehr
 */
final class VorbisComment extends \Vollbehr\Media\Flac\MetadataBlock
{
    private readonly \Vollbehr\Media\Vorbis\Header\Comment $_impl;

    /**
     * Constructs the class with given parameters and parses object related data using the vorbis comment implementation
     * class {@see \Vollbehr\Media\Vorbis\Header\Comment}.
     * @param \Vollbehr\Io\Reader $reader The reader object.
     */
    public function __construct($reader)
    {
        parent::__construct($reader);
        $this->_impl = new \Vollbehr\Media\Vorbis\Header\Comment($this->_reader, ['vorbisContext' => false]);
    }

    /**
     * Forward all calls to the vorbis comment implementation class {@see \Vollbehr\Media\Vorbis\Header\Comment}.
     * @param string $name The method name.
     * @param Array $arguments The method arguments.
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func([$this, $name], $arguments);
        }
        try {
            return $this->_impl->$name($arguments);
        } catch (\Vollbehr\Media\Vorbis\Exception $e) {

            throw new \Vollbehr\Media\Flac\Exception($e->getMessage());
        }
    }

    /**
     * Forward all calls to the vorbis comment implementation class {@see \Vollbehr\Media\Vorbis\Header\Comment}.
     * @param string $name The field name.
     */
    public function __get($name)
    {
        if (method_exists($this, 'get' . ucfirst($name))) {
            return call_user_func([$this, 'get' . ucfirst($name)]);
        }
        if (method_exists($this->_impl, 'get' . ucfirst($name))) {
            return call_user_func([$this->_impl, 'get' . ucfirst($name)]);
        }
        try {
            return $this->_impl->__get($name);
        } catch (\Vollbehr\Media\Vorbis\Exception $e) {

            throw new \Vollbehr\Media\Flac\Exception($e->getMessage());
        }
    }

    /**
     * Forward all calls to the vorbis comment implementation class {@see \Vollbehr\Media\Vorbis\Header\Comment}.
     * @param string $name The field name.
     * @param string $name The field value.
     */
    public function __set($name, $value)
    {
        if (method_exists($this, 'set' . ucfirst($name))) {
            call_user_func([$this, 'set' . ucfirst($name)], $value);
        } else {
            try {
                return $this->_impl->__set($name, $value);
            } catch (\Vollbehr\Media\Vorbis\Exception $e) {

                throw new \Vollbehr\Media\Flac\Exception($e->getMessage());
            }
        }

        return null;
    }

    /**
     * Forward all calls to the vorbis comment implementation class {@see \Vollbehr\Media\Vorbis\Header\Comment}.
     * @param string $name The field name.
     * @return boolean
     */
    public function __isset($name)
    {
        try {
            return $this->_impl->__isset($name);
        } catch (\Vollbehr\Media\Vorbis\Exception $e) {

            throw new \Vollbehr\Media\Flac\Exception($e->getMessage());
        }
    }

    /**
     * Forward all calls to the vorbis comment implementation class {@see \Vollbehr\Media\Vorbis\Header\Comment}.
     * @param string $name The field name.
     */
    public function __unset($name)
    {
        try {
            $this->_impl->__unset($name);
        } catch (\Vollbehr\Media\Vorbis\Exception $e) {

            throw new \Vollbehr\Media\Flac\Exception($e->getMessage());
        }
    }
}
