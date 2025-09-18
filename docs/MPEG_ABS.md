> **Summary:** Developer Reference Guide: `Vollbehr\\Media\\Mpeg\\Abs`
> **Labels:** implementation,mpeg-audio


# PHP Reader Documentation: `Vollbehr\Media\Mpeg\Abs`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Media\Mpeg\Abs` parses MPEG-1/2 audio bit streams (MP1/MP2/MP3). It understands legacy
CBR frames and common VBR extensions (Xing, VBRI, LAME) to deliver accurate duration and bitrate
statistics without decoding the audio payload.

![Diagram](model/model.mpeg.abs.png)

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Mpeg/Abs.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Media/Mpeg/Abs.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - `Vollbehr\Bit\Twiddling`
  - `Vollbehr\Media\Mpeg\Exception`
  - `Vollbehr\Media\Mpeg\Abs\Frame`

Constructor options:

- `readmode`: `Abs::READ_MODE_LAZY` (default) or `Abs::READ_MODE_FULL`
- `estimatePrecision`: number of frames to sample in lazy mode (default: 1000)

## Examples
### Inspect basic properties

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Mpeg\Abs;

$abs = new Abs('/path/to/example.mp3');

printf("Estimated length: %s\n", $abs->getFormattedLengthEstimate());
printf("Estimated bitrate: %d kbps\n", $abs->getBitrateEstimate());

printf("Exact length: %s\n", $abs->getFormattedLength());
printf("Exact bitrate: %d kbps\n", $abs->getBitrate());
```

### Enumerate frames lazily

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Mpeg\Abs;
use Vollbehr\Media\Mpeg\Abs\Frame;

$abs = new Abs('/path/to/example.mp3', ['readmode' => Abs::READ_MODE_LAZY]);

foreach ($abs->getFrames() as $frame) {
    /** @var Frame $frame */
    printf(
        "Offset: %d, Bitrate: %d kbps, Sample rate: %d Hz\n",
        $frame->getOffset(),
        $frame->getBitrate(),
        $frame->getSampleRate()
    );
}
```

### Calculate bitrate distribution

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Mpeg\Abs;

$abs        = new Abs('/path/to/example.mp3', ['readmode' => Abs::READ_MODE_FULL]);
$histogram  = [];
$frameCount = 0;

foreach ($abs->getFrames() as $frame) {
    $bitrate = $frame->getBitrate();
    $histogram[$bitrate] = ($histogram[$bitrate] ?? 0) + 1;
    $frameCount++;
}

foreach ($histogram as $bitrate => $count) {
    printf("%3d kbps: %.2f%%\n", $bitrate, ($count / $frameCount) * 100);
}
```

Lazy mode keeps memory usage low by reading frames on demand. Switch to full mode when you need to
iterate over every frame repeatedly.

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AMPEG>
