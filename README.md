# PHP Reader

PHP Reader is an object-oriented toolkit for inspecting and authoring media metadata. It ships with
parsers and writers for ASF (WMA/WMV), ID3 (v1 and v2), ISO Base Media File Format variants (MP4,
M4A, QuickTime, iTunes metadata), MPEG Audio Bit Streams, MPEG Program Streams, and MIME magic
signatures. The library targets modern PHP runtimes, follows the PSR coding standards, and includes
a comprehensive test suite and documentation set.

## Features
| Component | Capabilities | Read | Write | Typical extensions |
| --- | --- | --- | --- | --- |
| `Vollbehr\\Media\\Asf` | Advanced Systems Format containers, including metadata objects | ✓ | ✓ | ASF, WMA, WMV |
| `Vollbehr\\Media\\Flac` | Free Lossless Audio Codec (FLAC) | Planned | ✓ | - | FLAC |
| `Vollbehr\\Media\\Id3v1` | ID3v1.0 and ID3v1.1 tags | ✓ | ✓ | MP3 |
| `Vollbehr\\Media\\Id3v2` | ID3v2.3.0 and ID3v2.4.0 frames | ✓ | ✓ | MP3 |
| `Vollbehr\\Media\\Iso14496` | ISO/IEC 14496-12 Base Media File Format (QuickTime/MP4) and Extensions (iTunes ILST, Nero NDRM, and ID32) | ✓ | ✓ | MP4, M4A, M4V, 3GP, MOV, QT |
| `Vollbehr\\Media\\Iso14496` | ISO/IEC 23001-7 Extensions (Common Encryption TENC, PSSH) | ✓ | ✓ | MP4, M4A, M4V, 3GP, MOV, QT|

| `Vollbehr\\Media\\Mpeg\\Abs` | MPEG Audio Bit Stream (ISO/IEC 11172-3, 13818-3) | ✓ | – | MP1, MP2, MP3 |
| `Vollbehr\\Media\\Mpeg\\Ps` | MPEG Program Stream (ISO/IEC 11172-1, 13818-1) with duration and stream descriptors | ✓ | – | MPG, MPEG, VOB, EVO |
| `Vollbehr\\Media\\Riff` | RIFF or Resource Interchange File Format | ✓ | – | RIFF |
| `Vollbehr\\Media\\Riff` | RIFF Extensions (ID3v2, WAVE, Broadcast WAV) | ✓ | – | WAV, BWAV |
| `Vollbehr\\Media\\Vorbis` | Vorbis I CODEC in Ogg container | ✓ | – | OGG, OGA, OGV, SPX |
| `Vollbehr\\Mime\\Magic` | MIME magic signature database reader | ✓ | – | `magic`, `magic.mime` |

Utility classes such as `Vollbehr\Io\Reader`, `Vollbehr\Io\Writer`, and `Vollbehr\Bit\Twiddling`
are bundled for binary parsing and bitwise manipulation.


## Feature Requests

| Capabilities | Typical extensions | Issue to vote |
| --- | --- | --- | --- | --- |
| EXIF or Exchangeable Image File Format | JPEG, TIFF, RIFF | Not yet implemented (vote for issue 18) |
| Flash Video | FLV, F4V, F4P, F4A, F4B | Not yet implemented (vote for issue 36) |
| MP4 / AVC profiles derived from ISO/IEC 14496-12 | | Not yet implemented as this requires access to commercial specifications. If you are in need of these, consider supporting the project by funding the commercial specifications.| 


## Requirements
- PHP 8.2 or newer
- Composer 

## Installation
Install the package in any Composer-enabled project:

```bash
composer require vollbehr/php-reader
```

For contributors:

```bash
composer install
composer test
```

Composer configures PSR-4 autoloading for the `Vollbehr\\` namespace, so examples simply rely on
`vendor/autoload.php`.

## Quick Start
```php
<?php

declare(strict_types=1);

use Vollbehr\Io\FileReader;
use Vollbehr\Media\Id3v2;

$reader = new FileReader('/path/to/audio-with-id3.mp3');
$id3 = new Id3v2($reader);

$artistFrame = $id3->getFramesByIdentifier('TPE1')[0];
echo $artistFrame->getText();
```


## Ready for Framework Integrations
The core library is framework-agnostic. Optional bridge packages provide first-class integrations:

- **Laravel** – [`vollbehr/php-reader-laravel`](packages/laravel-bridge) registers
  `PhpReaderServiceProvider`, publishes the package config, and exposes the `FileReaderFactory`
  through the service container.
- **Symfony** – [`vollbehr/php-reader-symfony-bundle`](packages/symfony-bundle) contributes
  `PhpReaderBundle` with configuration options such as `default_file_mode`.
- **Laminas** – [`vollbehr/php-reader-laminas`](packages/laminas-bridge) supplies a `Module`,
  `ConfigProvider`, and service manager factory for the `FileReaderFactory`.

See `docs/FrameworkIntegrations.md` for usage notes and example wiring in each framework.


## Release Notes (LATEST, 1.9, 2025-09-18)
- Modernised namespaces under `Vollbehr\` with PSR-4 autoloading
- Bridge packages for Laravel, Symfony, and Laminas offer native service registration and
  configuration helpers.
- Minimum PHP version bumped to 8.2 with typed properties, enums, and readonly members where
  applicable.


## Documentation
Component guides and API references live under `docs/` and are published at
https://developers.vollbehr.io/docs.


## Contributing
1. Fork and clone the repository.
2. Install dependencies with `composer install`.
3. Run `composer test` before submitting patches.
4. Open a pull request describing the change and any relevant tests.


## License
Released under the BSD-3-Clause license. See `LICENSE` for the full text.
