<?php

use MF\Parser\CallbackParser;

require_once __DIR__ . '/vendor/autoload.php';

$callbackParser = new CallbackParser();
$callback = $callbackParser->parseArrowFunction('($a, $b) => $a + $b');

var_dump($callback(2,3));
