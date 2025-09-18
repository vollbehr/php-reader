<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496\Box;

use Vollbehr\Io\Reader;
use Vollbehr\Io\Writer;
use Vollbehr\Media\Iso14496\Exception;

final class Data extends FullBox
{
    public const INTEGER           = 0x0;
    public const INTEGER_OLD_STYLE = 0x15;
    public const STRING            = 0x1;
    public const JPEG              = 0xd;
    public const PNG               = 0xe;

    private string $value = '';

    public function __construct(null | Reader | string $reader = null, array &$options = [])
    {
        parent::__construct($reader, $options);

        if ($reader === null) {
            return;
        }

        $this->_reader->skip(4);
        $data = $this->_reader->read($this->getOffset() + $this->getSize() - $this->_reader->getOffset());

        switch ($this->getFlags()) {
            case self::INTEGER:
            case self::INTEGER_OLD_STYLE:
                for ($i = 0, $length = strlen((string) $data); $i < $length; $i++) {
                    $this->value .= (string) ord($data[$i]);
                }
                break;
            case self::STRING:
            default:
                $this->value = $data;
                break;
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(mixed $value, ?int $type = null): void
    {
        $this->value = (string) $value;

        if ($type !== null) {
            $this->_flags = $type;

            return;
        }

        if (is_string($value)) {
            $this->_flags = self::STRING;

            return;
        }

        if (is_int($value)) {
            $this->_flags = self::INTEGER;
        }
    }

    public function __get(string $name): mixed
    {
        if ($name === 'data') {
            return $this;
        }

        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        throw new Exception('Unknown box/field: ' . $name);
    }

    public function getHeapSize(): int
    {
        return parent::getHeapSize() + 4 + strlen($this->value);
    }

    protected function _writeData(Writer $writer): void
    {
        parent::_writeData($writer);
        $writer->write("\0\0\0\0");

        switch ($this->getFlags()) {
            case self::INTEGER:
            case self::INTEGER_OLD_STYLE:
                for ($i = 0, $length = strlen($this->value); $i < $length; $i++) {
                    $writer->writeInt8((int) $this->value[$i]);
                }
                break;
            case self::STRING:
            default:
                $writer->write($this->value);
                break;
        }
    }
}
