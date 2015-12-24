# Events Library for PHP

Simple library to implement event emitting capability for PHP applications.

## Installation

### Install via Composer

Run the following command in the root directory of your project:
```sh
composer require gamegos/events:*
```

## Basic Usage

```php
$eventManager = new Gamegos\Events\EventManager();
// Attach a callback to an event named 'foo'.
$eventManager->attach(
    'foo',
    function (Gamegos\Events\EventInterface $e) {
        echo sprintf('Handled "%s" event with subject "%s".', $e->getName(), $e->getSubject());
    }
);
// Trigger the 'foo' event with a subject ('bar').
$eventManager->trigger('foo', 'bar');
```
The above example will output:

    Handled "foo" event with subject "bar".

## Development

### Installation

- Clone the repository.
- Run `composer install`.

### Unit Tests

You can run the unit tests with the following command:

```sh
./vendor/bin/phpunit
```

#### Code Coverage
If [xdebug](http://xdebug.org/) extension is installed and enabled for PHP CLI, [PHPUnit](https://phpunit.de/) will
generate code coverage reports (in html format) will be generated under `./log` directory. If [xdebug](http://xdebug.org/)
is installed but not enabled for CLI, you may load it at the runtime by running the following command:
```sh
php -d zend_extension=xdebug.so ./vendor/bin/phpunit
```

