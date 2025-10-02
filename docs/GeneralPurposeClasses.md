> **Summary:** Utility classes shared across PHP Reader components
> **Labels:** io,bit-operations


# PHP Reader Documentation: Utility Classes
By [svollbehr](https://github.com/svollbehr)

The `Vollbehr\Io` and `Vollbehr\Bit` namespaces provide reusable building blocks that higher level
media parsers depend on. This guide highlights their roles and supplies runnable examples that the
test suite executes under `tests/Example` to ensure the snippets stay in sync with the codebase.

## FileReader Class
`Vollbehr\Io\FileReader` wraps a filesystem stream and exposes typed read helpers.

```php
<?php

declare(strict_types=1);

use Vollbehr\Io\FileReader;

$path = tempnam(sys_get_temp_dir(), 'php-reader-');
file_put_contents($path, pack('Cvv', 0x2a, 0x1234, 0x4567));

$reader = new FileReader($path);

$firstByte   = $reader->readUInt8();      // 0x2a
$littleWord  = $reader->readUInt16LE();   // 0x1234
$bigWord     = $reader->readUInt16BE();   // 0x4567

$reader->close();
unlink($path);
```

The reader tracks the current offset and supports seeking via `setOffset()` or `skip()`.

## StringReader Class
When an in-memory buffer is more convenient, `Vollbehr\Io\StringReader` offers the same API as
`FileReader` without touching the filesystem.

```php
<?php

declare(strict_types=1);

use Vollbehr\Io\StringReader;

$reader = new StringReader(pack('VJ', 0x01020304, 0x0000000000003039));

$littleEndian = $reader->readUInt32LE();  // 0x01020304
$bigEndian    = $reader->readUInt64BE();  // 0x0000000000003039
```

## Writer Classes
`Vollbehr\Io\FileWriter` and `Vollbehr\Io\StringWriter` mirror the reader API and provide typed
write helpers. Writers automatically grow their underlying storage and can be rewound for further
updates.

```php
<?php

declare(strict_types=1);

use Vollbehr\Io\StringWriter;

$writer = new StringWriter();
$writer->writeUInt8(0x1f);
$writer->writeUInt32BE(0xfeedbabe);

$buffer = $writer->toString();            // "\x1f\xfe\xed\xba\xbe"
```

## Twiddling Class
`Vollbehr\Bit\Twiddling` exposes helpers for testing and manipulating bit fields. The media
parsers lean on these methods when decoding header flags.

```php
<?php

declare(strict_types=1);

use Vollbehr\Bit\Twiddling;

$flags = 0b0000_1010;

Twiddling::setBit($flags, 1);             // now 0b0000_1011
$isSet = Twiddling::testBit($flags, 3);   // true
Twiddling::clearBit($flags, 3);           // back to 0b0000_0011

$slice = Twiddling::getValue(0xf2b4, 8, 15); // extract 0xf2
```

For the complete API surface, consult the generated reference at
<https://developers.vollbehr.io/docs/api/Vollbehr/Io/Reader> and
<https://developers.vollbehr.io/docs/api/Vollbehr/Bit/Twiddling>.
