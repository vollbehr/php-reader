> **Summary:** Developer Reference Guide: `Vollbehr\\Media\\Id3v2`
> **Labels:** implementation,id3v2


# PHP Reader Documentation: `Vollbehr\Media\Id3v2`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Media\Id3v2` implements the ID3v2.3.0 and ID3v2.4.0 tag specifications. The parser can
read, modify, and write frames in MP3 files as well as any other container that embeds an ID3v2 tag.

![Diagram](model/model.id3v2.png)

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Id3v2.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Media/Id3v2.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - `Vollbehr\Media\Id3\Header`
  - `Vollbehr\Media\Id3\Frame`
  - `Vollbehr\Media\Id3\Exception`

All frame subclasses live under `Vollbehr\Media\Id3\Frame` and follow the naming convention defined
in the specification (e.g. `Tit2` for the title frame, `Apic` for attached pictures).

## Examples
### Retrieve selected frames

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Id3v2;

$id3 = new Id3v2('/path/to/example.mp3');

$titleFrame = $id3->getFramesByIdentifier('TIT2')[0] ?? null;
if ($titleFrame !== null) {
    printf("Title: %s\n", $titleFrame->getText());
}

$artworkFrame = $id3->getFramesByIdentifier('APIC')[0] ?? null;
if ($artworkFrame !== null) {
    printf("Artwork MIME type: %s\n", $artworkFrame->getMimeType());
}
```

### Use shorthand accessors

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Id3v2;

$id3 = new Id3v2('/path/to/example.mp3');

if (isset($id3->tit2)) {
    printf("Title: %s\n", $id3->tit2->text);
}

if ($id3->hasFrame('TPE1')) {
    printf("Artist: %s\n", $id3->tpe1->text);
}
```

The shorthand accessors automatically create missing frames when first accessed. To check for
existence without creating a new frame, prefer `hasFrame()` or `isset($id3->tpe1)`.

### Extract all text frames

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Id3v2;
use Vollbehr\Media\Id3\TextFrame;

$id3 = new Id3v2('/path/to/example.mp3');

$textFrames = [];
foreach ($id3->getFramesByIdentifier('T*') as $frame) {
    if ($frame instanceof TextFrame) {
        $textFrames[$frame->getIdentifier()] = $frame->getText();
    }
}

print_r($textFrames);
```

### Write or update frames

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Id3v2;
use Vollbehr\Media\Id3\Frame\Tit2;
use Vollbehr\Media\Id3\Frame\Tpe1;

$id3 = new Id3v2('/path/to/example.mp3');

$title = new Tit2();
$title->setText('My Song Title');
$id3->addFrame($title);

$artist = new Tpe1();
$artist->setText('Vollbehr Systems');
$id3->addFrame($artist);

$id3->write();
```

Wrap the constructor in a `try/catch` block to surface IO errors or corrupted tags as
`Vollbehr\Media\Id3\Exception` instances.

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AID3v2>
