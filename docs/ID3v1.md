> **Summary:** Developer Reference Guide: `Vollbehr\\Media\\Id3v1`
> **Labels:** implementation,id3v1


# PHP Reader Documentation: `Vollbehr\Media\Id3v1`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Media\Id3v1` offers a complete implementation of the ID3v1.0 and ID3v1.1 tag formats.
The class supports both reading and writing tag frames attached to MPEG Layer III (MP3) files and any
other container that stores an ID3v1 footer.

![Diagram](model/model.id3v1.png)

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Id3v1.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Media/Id3v1.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - `Vollbehr\Media\Id3\Exception`

## Examples
### Read ID3v1 fields

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Id3v1;

$id3 = new Id3v1('/path/to/example.mp3');

printf("Title: %s\n", $id3->getTitle());
printf("Artist: %s\n", $id3->getArtist());
printf("Album: %s\n", $id3->getAlbum());
printf("Year: %s\n", $id3->getYear());
```

### Use shorthand properties

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Id3v1;

$id3 = new Id3v1('/path/to/example.mp3');

printf("Genre: %s\n", $id3->genre);
$id3->title = trim($id3->title . ' (Remastered)');
$id3->write();
```

### Create a new tag from scratch

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Id3v1;

$id3 = new Id3v1();
$id3->setTitle('New Track');
$id3->setArtist('Vollbehr Systems');
$id3->setAlbum('PHP Reader Sampler');
$id3->setYear('2025');
$id3->setGenre(Id3v1::GENRE_ROCK);
$id3->setTrack(5); // switches to ID3v1.1 automatically

$id3->write('/path/to/example.mp3');
```

Wrap tag operations in `try/catch` blocks to handle unreadable files or missing tags gracefully. All
errors surface as `Vollbehr\Media\Id3\Exception` instances.

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AID3v1>
