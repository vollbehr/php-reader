<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496\Box;

use Vollbehr\Io\Reader;
use Vollbehr\Media\Iso14496\Box\Ilst\Container;

/**
 * PHP Reader
 * @copyright (c) 2008-2012 Sven Vollbehr, 2024-2025 Vollbehr Systems AB
 * @license   BSD-3-Clause
 */
final class Ilst extends Box
{
    public function __construct(null|Reader|string $reader = null, array &$options = [])
    {
        parent::__construct($reader, $options);
        $this->setContainer(true);

        if ($reader === null) {
            return;
        }

        $this->constructBoxes(Container::class);
    }

    public function __get(string $name): mixed
    {
        if (strlen($name) === 3) {
            $name = "\xa9" . $name;
        }

        if ($name[0] === '_') {
            $name = "\xa9" . substr($name, 1, 3);
        }

        if ($this->hasBox($name)) {
            $boxes = $this->getBoxesByIdentifier($name);

            return $boxes[0]->data;
        }

        $getter = 'get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }

        return $this->addBox(new Container($name))->data;
    }
}
