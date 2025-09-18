> **Summary:** Developer Reference Guide: `Vollbehr\\Media\\Vorbis`
> **Labels:** implementation,vorbis


# PHP Reader Documentation: `Vollbehr\Media\Vorbis`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Media\Vorbis` reads Vorbis I headers and comment metadata. Pair it with
`Vollbehr\Media\Ogg\Reader` to parse Vorbis streams wrapped inside Ogg containers.

![Diagram](model/model.vorbis.png)

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Vorbis.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Media/Vorbis.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - `Vollbehr\Media\Ogg\Reader`
  - `Vollbehr\Media\Vorbis\Header\Comment`
  - `Vollbehr\Media\Vorbis\Header\Identification`
  - `Vollbehr\Media\Vorbis\Exception`

## Examples
### Fetch Vorbis comments

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Ogg\Reader as OggReader;
use Vollbehr\Media\Vorbis;

$vorbis = new Vorbis(new OggReader('/path/to/example.ogg'));
$comments = $vorbis->getCommentHeader();

printf("Title: %s\n", $comments->getTitle());
printf("Artist: %s\n", $comments->getArtist());
printf("Vendor: %s\n", $comments->getVendor());
```

### Iterate over all fields

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Ogg\Reader as OggReader;
use Vollbehr\Media\Vorbis;

$vorbis    = new Vorbis(new OggReader('/path/to/example.ogg'));
$comments  = $vorbis->commentHeader;   // shorthand accessor

foreach ($comments->getComments() as $name => $values) {
    printf("%s: %s\n", strtoupper($name), implode(', ', $values));
}
```

### Access multiple values for a key

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Ogg\Reader as OggReader;
use Vollbehr\Media\Vorbis;

$vorbis  = new Vorbis(new OggReader('/path/to/example.ogg'));
$artists = $vorbis->getCommentHeader()->getCommentsByName('artist');

foreach ($artists as $artist) {
    printf("Artist alias: %s\n", $artist);
}
```

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AVorbis>
