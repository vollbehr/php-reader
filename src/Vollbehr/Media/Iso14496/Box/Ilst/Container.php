<?php

declare(strict_types=1);

namespace Vollbehr\Media\Iso14496\Box\Ilst;

use Vollbehr\Io\Reader;
use Vollbehr\Media\Iso14496\Box;
use Vollbehr\Media\Iso14496\Box\Data;

final class Container extends Box
{
    public function __construct(null | Reader | string $reader = null, array &$options = [])
    {
        parent::__construct(is_string($reader) ? null : $reader, $options);
        $this->setContainer(true);

        if (is_string($reader)) {
            $this->setType($reader);
            $this->addBox(new Data());

            return;
        }

        $this->constructBoxes();
    }
}
