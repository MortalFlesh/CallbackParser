CallbackParser
==============

[![Latest Stable Version](https://img.shields.io/packagist/v/mf/callback-parser.svg)](https://packagist.org/packages/mf/callback-parser)
[![Build Status](https://travis-ci.org/MortalFlesh/CallbackParser.svg?branch=master)](https://travis-ci.org/MortalFlesh/CallbackParser)
[![Coverage Status](https://coveralls.io/repos/github/MortalFlesh/CallbackParser/badge.svg?branch=master)](https://coveralls.io/github/MortalFlesh/CallbackParser?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/mf/callback-parser.svg)](https://packagist.org/packages/mf/callback-parser)
[![License](https://img.shields.io/packagist/l/mf/callback-parser.svg)](https://packagist.org/packages/mf/callback-parser)

PHP parser for arrow functions
> This library is no longer supported, since the arrow functions are natively in PHP 7.4 - https://www.php.net/manual/en/functions.arrow.php

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Arrow Functions](#arrow-functions)
- [How does it work](#how-does-it-work)
- [Possibly WTF?](#wtf)
- [80:20 - Simple](#80-20)

## <a name="requirements"></a>Requirements
- PHP 7.1
- `eval()` function for parsing [Arrow Functions](#arrow-functions)


## <a name="installation"></a>Installation:
```
composer require mf/callback-parser
```


## <a name="arrow-functions"></a>Arrow Functions

### Usage:
```php
$callbackParser = new CallbackParser();
$callback = $callbackParser->parseArrowFunction('($a, $b) => $a + $b');

var_dump($callback(2, 3));  // int 5
```

### With Custom Exception
```php
$callbackParser = new CallbackParser(App\MyCustomException::class);

$callbackParser->parseArrowFunction('invalid');  // throws App\MyCustomException
```


## <a name="how-does-it-work"></a>How does it work?
- it parses function from string and evaluate it with `eval()`


## <a name="wtf"></a>Possibly WTF?
This parser can parse an arrow function into PHP to be execute as usual. 
But this process could be a little bit more complex than just `eval` it.
You can check `CallbackParserTest::provideInvalidFunction()` for examples.

### Namespaces of parameters
For example namespace of class for given parameter type.
```php
(SimpleEntity $entity) => $entity->getId()
```
This example above shows an `INVALID` arrow function to be parsed (yet?).
Theres more reasons for this is an `invalid` one:
- callback is parsed and `eval`ed elsewhere of scope, where you give such a callback
- so `CallbackParser` does not know `SimpleEntity` full class name

There is more ways to 'fix' it, like:
- you can register a class map of allowed parameter types and parser could find a relevant one and
 use a full class name from the map, but IMHO this could be more complex than it should be
- parser could also find a relevant class in you entire project and magically use one of the most relevant, 
but it's a dark magic and I'd rather avoid it

#### Question is - is it really necessary?
I dont think so. Because PHP is quite powerful (`weak`) and allows you
to use class methods of an object even if you don't know what they are.
But since the original purpose of this parser was to parse a callbacks on [Collections](https://github.com/MortalFlesh/MFCollectionsPHP),
you have other ways to know and verify a object type in parameter, so you can simply use those methods right away.

```php
$list = new Generic\ListCollection(SimpleEntity::class);
$list->add(new SimpleEntity(1));
$list->add(new SimpleEntity(2));

$ids = $list->map('($entity) => $entity->getId()');

var_dump($ids);

//array (size=2)
//  0 => int 1
//  1 => int 2
```

## <a name="80-20"></a>Simple simpler and complex still simply - 80:20
IMHO this parser allows you to parse simple functions simply, and you can still write a complex functions like PHP allows you.
