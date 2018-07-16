<?php declare(strict_types=1);

namespace MF\Parser;

use Assert\Assert;
use Assert\Assertion;

class CallbackParser
{
    const FUNCTION_REGEX = '#^\(([A-z0-9, \$]*?){1}\)[ ]?\=\>[ ]?(.{1,})$#u';
    const PARAM_REGEX = '#^\$[A-z0-9\_]{1,}$#';
    const ARGUMENT_SEPARATOR = ',';
    const ARRAY_FUNCTION_OPERATOR = '=>';

    /**
     * @param string|callable $func
     * @return callable
     */
    public function parseArrowFunction($func): callable
    {
        if (is_callable($func)) {
            return $func;
        }

        Assert::that($func)
            ->string('Array function has to be string')
            ->notEmpty('Array function has to be not-empty string');

        $func = trim($func);
        Assertion::regex($func, self::FUNCTION_REGEX, 'Array function is not in right format');

        $parts = explode(self::ARRAY_FUNCTION_OPERATOR, $func, 2);  // ['($a, $b)', '$a + $b']
        $params = explode(self::ARGUMENT_SEPARATOR, str_replace(['(', ')', ' '], '', $parts[0]));   // ['$a', '$b']

        $this->assertParamsSyntax($params);

        $functionBody = trim(trim($parts[1], '; {}'), '; ');  // '$a + $b'

        if (mb_strpos($functionBody, 'return') === false) {
            $callback = sprintf('$callback = function(%s){return %s;};', implode(',', $params), $functionBody);
        } else {
            $callback = sprintf('$callback = function(%s){%s;};', implode(',', $params), $functionBody);
        }
        eval($callback);

        if (is_callable($callback)) {
            return $callback;
        }

        throw new \InvalidArgumentException('Array function is not in right format');
    }

    private function assertParamsSyntax(array $params): void
    {
        foreach (array_filter($params) as $param) {
            Assertion::regex($param, self::PARAM_REGEX, 'Params are not in right format');
        }
    }
}
