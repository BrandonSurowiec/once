# A magic memoization function

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/once.svg?style=flat-square)](https://packagist.org/packages/spatie/once)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/once/master.svg?style=flat-square)](https://travis-ci.org/spatie/once)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/once.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/once)
[![StyleCI](https://styleci.io/repos/73020509/shield?branch=master)](https://styleci.io/repos/73020509)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/once.svg?style=flat-square)](https://packagist.org/packages/spatie/once)

This package contains a `once` function. You can pass a `callable` to it. Here's quick example:

```php
class MyClass
{
    function getNumber()
    {
        return once(function () {
            return rand(1, 10000);
        });
    }
}
```
 
No matter how many times you run `(new MyClass())->getNumber()` inside the same request  you'll always get the same number.

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Installation

You can install the package via composer:

``` bash
composer require spatie/once
```

## Usage

The `once` function accepts a `callable`.

```php
class MyClass
{
    function getNumber()
    {
        return once(function () {
            return rand(1, 10000);
        });
    }
}
```

No matter how many times you run `(new MyClass())->getNumber()` you'll always get the same number.

The `once` function will only run once per combination of argument values the containing method receives.

```php
class MyClass
{
    public function getNumberForLetter($letter)
    {
        return once(function () use ($letter) {
            return $letter . rand(1, 10000000);
        });
    }
}
```

So calling `(new MyClass())->getNumberForLetter('A')` will always return the same result, but calling `(new MyClass())->getNumberForLetter('B')` will return something else.

## Behind the curtains

Let's go over [the code of the `once` function](https://github.com/spatie/once/blob/4954c54/src/functions.php) to learn how all the magic works.

In short: it will execute the given callable and save the result in the static `$values` property of `Spatie\Once\Cache`. When we detect that `once` has already run before, we're just going to return the value stored inside the `$values` array instead of executing the callable again.

The first thing it does it calling [`debug_backtrace`](http://php.net/manual/en/function.debug-backtrace.php). We'll use the output to determine in which function and class `once` is called and to get access to the `object` that function is running in. Yeah, we're already in voodoo-land. The output of the `debug_backtrace` is passed to a new instance of `Backtrace`. That class is just a simple wrapper so we can work more easily with the backtrace.

```php
$trace = debug_backtrace(
    DEBUG_BACKTRACE_PROVIDE_OBJECT, 2
)[1];

$backtrace = new Backtrace($trace);
```

Next, we're going to check if `once` was called from within an object. If it was called from a static method or outside a class, we just bail out.

```php
if (! $object = $backtrace->getObject()) {
   throw new Exception('Cannot use `once` outside a class');
}
```

Now that we're certain `once` is called within an instance of a class we're going to calculate a `hash` of the backtrace. This hash will be unique per function `once` was called in and the values of the arguments that function receives.

```php
$hash = $backtrace->getArgumentHash();
```

Finally we will check if there's already a value stored for the given hash. If not, then execute the given `$callback` and store the result in `Spatie\Once\Cache`. In the other case just return the value from that cache (the `$callback` isn't executed). 

```php
if (! Cache::has($object, $hash)) {
    $result = call_user_func($callback, $backtrace->getArguments());

    Cache::set($object, $hash, $result);
}

return Cache::get($object, $hash);
```

## Caveats

- you can only use the `once` function in non-static class methods

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

Credit for the idea of the `once` function goes to [Taylor Otwell](https://twitter.com/taylorotwell/status/794622206567444481). The code for this package is based upon the code he was kind enough to share with us.

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
