# Events Library for PHP

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/gamegos/events-php/master/LICENSE)
[![Build Status](https://travis-ci.org/gamegos/events-php.svg?branch=master)](https://travis-ci.org/gamegos/events-php)
[![codecov.io](https://codecov.io/github/gamegos/events-php/coverage.svg?branch=master)](https://codecov.io/github/gamegos/events-php?branch=master)

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

