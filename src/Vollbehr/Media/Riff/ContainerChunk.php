<?php

declare(strict_types=1);

namespace Vollbehr\Media\Riff;

use Vollbehr\Media\Riff\Chunk\ListChunk;

/**
 * PHP Reader
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */
abstract class ContainerChunk extends Chunk
{
    protected string $_type;

    /** @var array<int, Chunk> */
    private array $_chunks = [];

    /**
     * Constructs the class with given parameters and options.
     */
    public function __construct(\Vollbehr\Io\Reader $reader)
    {
        parent::__construct($reader);
        $startOffset = $this->_reader->getOffset();
        $this->_type = $this->_reader->read(4);

        while (($this->_reader->getOffset() - $startOffset) < $this->_size) {
            $offset = $this->_reader->getOffset();

            $identifier = $this->_reader->read(4);
            $size       = $this->_reader->readUInt32LE();

            $this->_reader->setOffset($offset);

            $className = $this->resolveChunkClass(rtrim((string) $identifier, ' '));

            if ($className !== null && class_exists($className)) {
                $this->_chunks[] = new $className($this->_reader);
                $this->_reader->setOffset($offset + 8 + $size);
                continue;
            }

            trigger_error(sprintf("Unknown RIFF chunk: '%s' skipped", $identifier), E_USER_WARNING);
            $this->_reader->skip(8 + $size);
        }
    }

    /**
     * Returns a four-character code that identifies the contents of the container chunk.
     */
    final public function getType(): string
    {
        return $this->_type;
    }

    /**
     * Sets the four-character code that identifies the contents of the container chunk.
     */
    final public function setType(string $type): void
    {
        $this->_type = $type;
    }
    /**
     * Returns all the chunks this chunk contains as an array.
     * @return array<int, Chunk>
     */
    final public function getChunks(): array
    {
        return $this->_chunks;
    }
    /**
     * Returns an array of chunks matching the given identifier or an empty array if no chunks matched the identifier.
    *
     * The identifier may contain wildcard characters '*' and '?'. The asterisk matches against zero or more characters,
     * and the question mark matches any single character.
    *
     * Please note that one may also use the shorthand $obj->identifier to access the first chunk with the identifier
     * given. Wildcards cannot be used with the shorthand.
     * @return array<int, Chunk>
     */
    final public function getChunksByIdentifier(string $identifier): array
    {
        $matches       = [];
        $searchPattern = '/^' . str_replace(['*', '?'], ['.*', '.'], $identifier) . '$/i';

        foreach ($this->_chunks as $chunk) {
            if (preg_match($searchPattern, rtrim($chunk->getIdentifier(), ' ')) === 1) {
                $matches[] = $chunk;
            }
        }

        return $matches;
    }
    /**
     * Magic function so that $obj->value will work. The method will first attempt to return the first contained chunk
     * whose identifier matches the given name, and if not found, invoke a getter method.
     * If there are no chunks or getter methods with the given name, an exception is thrown.
     */
    public function __get(string $name)
    {
        $chunks = $this->getChunksByIdentifier($name);
        if ($chunks !== []) {
            return $chunks[0];
        }

        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        throw new Exception('Unknown chunk/field: ' . $name);
    }

    private function resolveChunkClass(string $identifier): string
    {
        $identifierKey = strtolower($identifier);

        if ($identifierKey === 'list') {
            return ListChunk::class;
        }

        return '\\Vollbehr\\Media\\Riff\\Chunk\\' . ucfirst($identifierKey);
    }
}
