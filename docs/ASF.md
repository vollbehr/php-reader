> **Summary:** Developer Reference Guide: `Vollbehr\\Media\\Asf`
> **Labels:** implementation,asf


# PHP Reader Documentation: `Vollbehr\Media\Asf`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Media\Asf` provides a full implementation of Microsoft's Advanced Systems Format (ASF).
Use it to traverse the object hierarchy found in ASF, WMA, and WMV files, extract metadata, or write
updates back to disk. The parser cooperates with the reader and bit utility classes outlined in
[`GeneralPurposeClasses.md`](GeneralPurposeClasses.md).

![Diagram](model/model.asf.png)

Program streams consist of nested objects. PHP Reader maps each of them to a dedicated class so that
your application can reason about the structure without juggling raw byte offsets.

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Media/Asf.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Media/Asf.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - `Vollbehr\Io\Exception`
  - `Vollbehr\Media\Asf\Object\Container`
  - `Vollbehr\Media\Asf\Exception`

Related classes live under `Vollbehr\Media\Asf\Object\*` and mirror the ASF specification one to
one.

## ASF Objects
The following table lists the top-level objects an ASF file may include. Nested items indicate which
objects can appear under a parent container.

- `Vollbehr\Media\Asf\Object\Header`
  - `...\FileProperties`
  - `...\StreamProperties`
  - `...\HeaderExtension`
    - `...\ExtendedStreamProperties`
    - `...\AdvancedMutualExclusion`
    - `...\GroupMutualExclusion`
    - `...\StreamPrioritization`
    - `...\BandwidthSharing`
    - `...\LanguageList`
    - `...\Metadata`
    - `...\MetadataLibrary`
    - `...\IndexParameters`
    - `...\MediaObjectIndexParameters`
    - `...\TimecodeIndexParameters`
    - `...\Compatibility`
    - `...\AdvancedContentEncryption`
  - `...\CodecList`
  - `...\ScriptCommand`
  - `...\Marker`
  - `...\BitrateMutualExclusion`
  - `...\ErrorCorrection`
  - `...\ContentDescription`
  - `...\ExtendedContentDescription`
  - `...\ContentBranding`
  - `...\StreamBitrateProperties`
  - `...\ContentEncryption`
  - `...\ExtendedContentEncryption`
  - `...\DigitalSignature`
  - `...\Padding`
- `Vollbehr\Media\Asf\Object\Data`
- `Vollbehr\Media\Asf\Object\SimpleIndex`
- `Vollbehr\Media\Asf\Object\Index`
- `Vollbehr\Media\Asf\Object\MediaObjectIndex`
- `Vollbehr\Media\Asf\Object\TimecodeIndex`

Consult the API reference for the complete list of subordinate classes and properties.

## Examples
### Enumerate top-level objects

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Asf;

$asf = new Asf('/path/to/example.wmv');

foreach ($asf->getObjects() as $objectGroup) {
    foreach ($objectGroup as $object) {
        printf("%s at 0x%X (%d bytes)\n", $object::class, $object->getOffset(), $object->getSize());
    }
}
```

### Walk the hierarchy recursively

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Asf;
use Vollbehr\Media\Asf\Object\Container;

/**
 * @param Container $container
 * @param int       $depth
 */
function dumpContainer(Container $container, int $depth = 0): void
{
    foreach ($container->getObjects() as $groups) {
        foreach ($groups as $object) {
            printf(
                "%s%s (%s)\n",
                str_repeat('    ', $depth),
                $object::class,
                $object->getIdentifier()
            );

            if ($object instanceof Container) {
                dumpContainer($object, $depth + 1);
            }
        }
    }
}

$asf = new Asf('/path/to/example.wma');
dumpContainer($asf);
```

### Access common metadata

```php
<?php

declare(strict_types=1);

use Vollbehr\Media\Asf;

$asf      = new Asf('/path/to/example.wma');
$header   = $asf->getHeader();
$props    = $header->fileProperties;
$duration = ($props->getPlayDuration() - $props->getPreroll()) / 10_000_000;

printf("Play duration: %.2f seconds\n", $duration);
```

The examples above rely on the magic accessors provided by container objects. When you need every
instance of a given identifier, prefer `getObjectsByIdentifier()` for deterministic results.

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AASF>
- Open feature ideas: <https://github.com/vollbehr/php-reader/issues?q=is%3Aopen+label%3AASF+label%3Aenhancement>
