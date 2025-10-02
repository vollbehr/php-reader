> **Summary:** Developer Reference Guide: `Vollbehr\\Media\\Flac`
> **Labels:** implementation,flac


# PHP Reader Documentation: `Vollbehr\Media\Flac`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Media\Flac` parses the metadata blocks found in Free Lossless Audio Codec (FLAC) streams.
The class focuses on descriptive metadata such as STREAMINFO, VORBIS_COMMENT, and PICTURE blocks;
decoding audio frames remains out of scope and can be layered on top if required.

![Diagram](model/model.flac.png)

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Flac.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Media/Flac.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - `Vollbehr\Media\Flac\MetadataBlock`
  - `Vollbehr\Media\Flac\Exception`
  - `Vollbehr\Media\Vorbis\Header\Comment`

## Metadata Blocks
FLAC supports up to 128 metadata block types. PHP Reader currently exposes the following:

- `Streaminfo`
- `Application`
- `Padding`
- `Seektable`
- `VorbisComment`
- `Cuesheet`
- `Picture`

Each block maps to a dedicated PHP class under `Vollbehr\Media\Flac\MetadataBlock`.

## Examples
### Access common metadata

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Flac;

$flac = new Flac('/path/to/example.flac');

if ($flac->hasMetadataBlock(Flac::PICTURE)) {
    $picture = $flac->getPicture();
    printf("Cover art: %s (%d bytes)\n", $picture->getMimeType(), strlen($picture->getData()));
}

$comment = $flac->getVorbisComment();
printf("Title: %s\n", $comment->getTitle());
printf("Artist: %s\n", $comment->getArtist());

$stream     = $flac->getStreaminfo();
$sampleRate = $stream->getSampleRate();
$samples    = $stream->getNumberOfSamples();
$duration   = $samples > 0 && $sampleRate > 0 ? $samples / $sampleRate : null;

if ($duration !== null) {
    printf("Duration: %.2f seconds\n", $duration);
}
```

### Using magic accessors

Container classes expose magic getters that forward to the first instance of a block or metadata
field. The snippet below mirrors the behaviour of the previous example while using the shorthand
property accessors.

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Flac;

$flac = new Flac('/path/to/example.flac');

if ($flac->hasMetadataBlock(Flac::PICTURE)) {
    printf("Cover art: %s (%d bytes)\n", $flac->picture->getMimeType(), strlen($flac->picture->getData()));
}

printf("Title: %s\n", $flac->vorbisComment->title);
printf("Artist: %s\n", $flac->vorbisComment->artist);

$duration = $flac->streaminfo->numberOfSamples / $flac->streaminfo->sampleRate;
printf("Duration: %.2f seconds\n", $duration);
```

## Further Reading
- Vorbis Comments: [`docs/Vorbis.md`](Vorbis.md)
- API reference for metadata blocks: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Flac/MetadataBlock.html>

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AFLAC>
