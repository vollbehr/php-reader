# PHP Reader Symfony Bundle

This bundle wires the `vollbehr/php-reader` file reader factory into the Symfony service container, giving you framework-native configuration and autoconfiguration support.

## Installation

```bash
composer require vollbehr/php-reader-symfony-bundle
```

If you're using Symfony Flex the bundle will be enabled automatically. Otherwise, register it in your `config/bundles.php`:

```php
return [
    // ...
    Vollbehr\Bridge\Symfony\PhpReaderBundle::class => ['all' => true],
];
```

## Configuration

Override the default configuration by creating `config/packages/php_reader.yaml`:

```yaml
php_reader:
  default_file_mode: 'rb'
```

You can access the factory directly from the container; this snippet mirrors the automated test harness:

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vollbehr\Support\FileReaderFactory;

$container = new ContainerBuilder();
$container->loadFromExtension('php_reader', ['default_file_mode' => 'rb']);
$container->compile();

/** @var FileReaderFactory $factory */
$factory = $container->get(FileReaderFactory::class);
$reader  = $factory->open('/path/to/audio.mp3');
```

## Versioning

Tag bundle releases in lockstep with `vollbehr/php-reader` so consumers can rely on compatible APIs across the ecosystem.
