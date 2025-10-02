# PHP Reader Laravel Bridge

A Laravel-oriented service provider that exposes the `vollbehr/php-reader` factory through the framework container and supports configuration publishing and optional facade aliases.

## Installation

```bash
composer require vollbehr/php-reader-laravel
```

The provider is auto-discovered. To customise the default file mode, publish the configuration:

```bash
php artisan vendor:publish --tag=php-reader-config
```

Edit the generated `config/php-reader.php` to override the `default_file_mode` value.

## Usage

Resolve the shared factory straight from the container. The example below matches the framework integration test and is easy to translate into a real application service:

```php
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Vollbehr\Bridge\Laravel\PhpReaderServiceProvider;
use Vollbehr\Support\FileReaderFactory;

final class StubApplication extends Container
{
    public function configPath(string $path = ''): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}

$app = new StubApplication();
$app->instance('config', new Repository([
    'php-reader' => [
        'default_file_mode' => 'rb',
    ],
]));

$provider = new PhpReaderServiceProvider($app);
$provider->register();
$provider->boot();

/** @var FileReaderFactory $factory */
$factory = $app->make(FileReaderFactory::class);
$reader  = $factory->open('/path/to/audio.mp3');
```

## Versioning

Keep bridge releases aligned with the core package's major and minor versions to avoid dependency drift for downstream applications.
