# PHP Reader Laminas Bridge

A lightweight integration layer that exposes the `vollbehr/php-reader` services through the Laminas ServiceManager. Include the module to auto-register the `Vollbehr\Support\FileReaderFactory` for both Laminas MVC and Mezzio applications.

## Installation

```bash
composer require vollbehr/php-reader-laminas
```

If you rely on laminas-component-installer the module will be enabled automatically. Otherwise, add it manually:

```php
return [
    'modules' => [
        // ...
        \Vollbehr\Bridge\Laminas\Module::class,
    ],
];
```

## Configuration

The bridge ships with a `php-reader` config namespace. Override the defaults in `config/autoload/*.php` if you need a custom file mode:

```php
return [
    'php-reader' => [
        'default_file_mode' => 'rb',
    ],
];
```

Once enabled, the ServiceManager exposes the shared factory. The example below mirrors the integration test and works for both MVC and Mezzio environments:

```php
use Laminas\ServiceManager\ServiceManager;
use Vollbehr\Bridge\Laminas\ConfigProvider;
use Vollbehr\Support\FileReaderFactory;

$config = (new ConfigProvider())();
$config['php-reader']['default_file_mode'] = 'rb';

$container = new ServiceManager($config['service_manager']);
$container->setService('config', $config);

/** @var FileReaderFactory $factory */
$factory = $container->get(FileReaderFactory::class);
$reader  = $factory->open('/path/to/audio.mp3');
```

## Versioning

Bridge releases track the core library's major and minor version. Tag the bridge with the same version number whenever you tag `vollbehr/php-reader` to keep dependency ranges aligned.
