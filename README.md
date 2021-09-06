# php-aop-cache
Aspect-Oriented Caching of Method Calls Using Runkit7

## Goals

This project allows you to decorate method calls with a caching behavior.
In other words, this means that, without modifing the source code of a foreign class, 
you can "plug" a cache mechanism to a choosen public method.

## When is it useful?

1. Before refactoring large code bases.
2. When dealing with third-party code.
3. To avoid licensing issues.
4. To avoid repeated code.
5. To easily test caching backends.

This is not a complete list, just some insights!

## Dependencies

- install and enable runkit7 
- php 7.2+

## What can be cached?

You must decorate only user defined methods. Native classes are not supported.

Ex: You cannot attach directly to PDO::query.

## How to use?

1. install with composer: `composer require rodrigovr/php-aop-cache`

2. as soon as possible, instantiate the Decorator with a given cache implementation:

```php
use rodrigovr\Aop;

...

$decorator = Cache\Decorator::create(new Cache\MemoryCache);
```

3. attach the decorator to any methods you want to cache results:

```php
$decorator->attachTo('SomeClass', 'methodName')
          ->attachTo('SomeClass', 'anotherMethodName')
          ->attachTo('AnotherClass', 'oneMoreMethod');
```

4. if needed, set how long (in seconds), results will be cached

```php
// cache results up to 5 minutes
$decorator->expires(300);
```

5. you can also limit cache entries by size (in bytes)
```php
// results over 10MB will not be saved
$decorator->limit(10000000);
```

6. multiple decorators are supported

```php
$applyMemoryCache = Cache\Decorator::create(new Cache\MemoryCache);
$applySessionCache = Cache\Decorator::create(new Cache\SessionCache);
$applyApcuCache = Cache\Decorator::create(new Cache\SessionCache);
```

7. you can create custom caching backends

```php
use rodrigovr\Aop;

class MyCache implements Cache\Storage {
    public function save($key, $value, $ttl) {
        // your custom save implementation
    }
    public function load($key) {
        // custom load implementation
    }
}

$decorator = Cache\Decorator::create(new MyCache)->expires(120);