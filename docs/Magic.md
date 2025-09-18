> **Summary:** Developer Reference Guide: `Vollbehr\\Mime\\Magic`
> **Labels:** implementation,mime


# PHP Reader Documentation: `Vollbehr\Mime\Magic`
By [svollbehr](https://github.com/svollbehr)

## Introduction
`Vollbehr\Mime\Magic` inspects binary signatures ("magic numbers") to determine MIME types and file
descriptions. It mirrors the behaviour of the Unix `file` utility and ships with a portable magic
syntax parser so you can reuse existing databases.

## Class Information

- Documentation: <https://developers.vollbehr.io/docs/api/Vollbehr/Mime/Magic.html>
- Source: <https://github.com/vollbehr/php-reader/blob/main/src/Vollbehr/Mime/Magic.php>
- Requirements:
  - `Vollbehr\Io\FileReader`
  - A magic database (text file) following the traditional `file(1)` syntax

## Using magic databases
The magic database contains one rule per line with the following columns:

1. Offset (optionally dependent on the previous match when prefixed with `>`)
2. Data type (`byte`, `short`, `string`, `beshort`, `lelong`, ...)
3. Expected value (literal or numeric)
4. Description or MIME type to return
5. Optional MIME encoding

Existing distributions such as FreeBSD, Linux, and macOS ship databases you can reuse. Copy the file
into your project or author a minimal subset tailored to the types you care about.

## Examples
### Determine MIME type

```php
<?php

declare(strict_types=1);

use Vollbehr\Mime\Magic;

$magic = new Magic(__DIR__ . '/magic');

$mimeType = $magic->getMimeType('/path/to/uploaded.bin', 'application/octet-stream');
printf("Detected MIME type: %s\n", $mimeType);
```

### Test for a specific type

```php
<?php

declare(strict_types=1);

use Vollbehr\Mime\Magic;

$magic = new Magic(__DIR__ . '/magic');

if ($magic->isMimeType('/path/to/file.pdf', 'application/pdf')) {
    // Accept the upload
}
```

Add new magic rules to the database file as your project encounters additional formats. The parser
supports line comments (prefix with `#`) and blank lines for readability.

## Issues
- GitHub tracker: <https://github.com/vollbehr/php-reader/issues?q=is%3Aissue+label%3AMagic>
