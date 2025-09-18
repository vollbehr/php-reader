> **Summary:** Developer Reference Guide: `Vollbehr\\Media\\Mpeg\\Ps`
> **Labels:** implementation,mpeg-program-stream


# PHP Reader Documentation: `Vollbehr\Media\Mpeg\Ps`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Media\Mpeg\Ps` parses MPEG Program Streams as defined in ISO/IEC 11172-1 and 13818-1. The
current implementation focuses on computing playback duration by analysing sequence headers and group
of picture (GOP) markers.

![Program Stream](model/model.mpeg-explained.png)

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Mpeg/Ps.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Media/Mpeg/Ps.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - `Vollbehr\Bit\Twiddling`
  - `Vollbehr\Media\Mpeg\Exception`

![Diagram](model/model.mpeg.ps.png)

## Examples
### Read playback length

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Mpeg\Ps;

$ps = new Ps('/path/to/example.mpg');

printf("Duration: %.2f seconds\n", $ps->getLength());
printf("Formatted: %s\n", $ps->getFormattedLength());
```

### Format duration manually

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Mpeg\Ps;

$ps     = new Ps('/path/to/example.vob');
$length = $ps->getLength();

$hours   = (int) floor($length / 3600);
$minutes = (int) floor(($length % 3600) / 60);
$seconds = $length % 60;

printf("Duration: %02d:%02d:%06.3f\n", $hours, $minutes, $seconds);
```

Program stream parsing is read-only today. If you need to modify GOP structures or mux new streams,
layer additional tooling on top of the reader primitives.

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AMPEG>
