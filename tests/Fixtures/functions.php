<?php declare(strict_types=1);

namespace MF\Parser\Fixtures;

class Functions
{
    public const hello = __NAMESPACE__ . '\hello';
}

function hello(string $name): string
{
    return sprintf('Hello %s!', $name);
}
