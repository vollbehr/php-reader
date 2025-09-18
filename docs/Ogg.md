> **Summary:** Developer Reference Guide: `Vollbehr\\Media\\Ogg\\Reader`
> **Labels:** implementation,ogg


# PHP Reader Documentation: `Vollbehr\Media\Ogg\Reader`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Media\Ogg\Reader` implements the container framing defined in RFC 3533. It exposes the
payload of each page so codec-specific readers—such as the Vorbis parser—can reconstruct their
packet streams.

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Ogg/Reader.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Media/Ogg/Reader.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - `Vollbehr\Media\Ogg\Exception`
  - `Vollbehr\Media\Ogg\Page`

## Examples
### Iterate over Ogg pages

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Ogg\Reader;

$reader = new Reader('/path/to/example.ogg');

foreach ($reader as $page) {
    printf(
        "Sequence #%d, Granule position %d, Payload bytes %d\n",
        $page->getSequenceNumber(),
        $page->getGranulePosition(),
        strlen($page->getSegmentData())
    );
}
```

### Pipe packets to the Vorbis decoder

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Ogg\Reader;
use Vollbehr\Media\Vorbis;

$ogg    = new Reader('/path/to/example.ogg');
$vorbis = new Vorbis($ogg);

printf("Sample rate: %d Hz\n", $vorbis->getSampleRate());
printf("Channels: %d\n", $vorbis->getChannels());
```

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AOgg>
