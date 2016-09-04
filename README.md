CallbackParser
==============




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
//composer.json
{
    "require": {
        "mf/callback-parser": "dev-master"
    },
    "repositories": [
        {
            "type": "vcs",
            "url":  "https://github.com/MortalFlesh/CallbackParser.git"
        }
    ]
}

// console
composer install
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
