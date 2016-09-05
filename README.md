CallbackParser
==============

[![Build Status](https://travis-ci.org/MortalFlesh/CallbackParser.svg?branch=master)](https://travis-ci.org/MortalFlesh/CallbackParser)
[![Coverage Status](https://coveralls.io/repos/github/MortalFlesh/CallbackParser/badge.svg?branch=master)](https://coveralls.io/github/MortalFlesh/CallbackParser?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/mf/callback-parser.svg)](https://packagist.org/packages/mf/callback-parser)
[![License](https://img.shields.io/packagist/l/mf/callback-parser.svg)](https://packagist.org/packages/mf/callback-parser)

PHP parser for arrow functions

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Arrow Functions](#arrow-functions)
- [How does it work](#how-does-it-work)

## <a name="requirements"></a>Requirements
- PHP 5.5
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

## <a name="how-does-it-work"></a>How does it work?
- it parses function from string and evaluate it with `eval()`
