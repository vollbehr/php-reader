# Framework Integrations

PHP Reader ships as a standalone library, but optional bridge packages wire it cleanly into common PHP frameworks. The examples below reuse the lightweight integration tests as practical usage guides.

## Laminas / Mezzio
Install the bridge and enable the module if your application does not use laminas-component-installer:

```bash
composer require vollbehr/php-reader-laminas
```

```php
// config/modules.config.php
return [
    // ...
    \Vollbehr\Bridge\Laminas\Module::class,
];
```

Fetch the shared factory from the service container:

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
$reader = $factory->open('/path/to/audio.mp3');
```

Set `php-reader.default_file_mode` in your Laminas configuration if you need a non-default file mode.

## Symfony
Require and enable the bundle:

```bash
composer require vollbehr/php-reader-symfony-bundle
```

```php
// config/bundles.php
return [
    // ...
    Vollbehr\Bridge\Symfony\PhpReaderBundle::class => ['all' => true],
];
```

Configure the optional default file mode and let autowiring inject the factory:

```yaml
# config/packages/php_reader.yaml
php_reader:
  default_file_mode: 'rb'
```

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Vollbehr\Support\FileReaderFactory;

$container = new ContainerBuilder();
$container->loadFromExtension('php_reader', ['default_file_mode' => 'rb']);
$container->compile();

/** @var FileReaderFactory $factory */
$factory = $container->get(FileReaderFactory::class);
$reader = $factory->open('/path/to/audio.mp3');
```

## Laravel
Install the bridge and publish the configuration if you want to override defaults:

```bash
composer require vollbehr/php-reader-laravel
php artisan vendor:publish --tag=php-reader-config
```

Resolve the factory from the container the same way the integration test does:

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
$reader = $factory->open('/path/to/audio.mp3');
```

In a real Laravel application you would type-hint `FileReaderFactory` in a controller or service and let the framework inject it automatically.

---

Each bridge keeps heavy logic in the core library, so upgrading `vollbehr/php-reader` to the next release automatically benefits all framework integrations.
