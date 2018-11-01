<?php declare(strict_types=1);

namespace MF\Parser;

use Assert\Assertion;
use Assert\AssertionFailedException;

class CallbackParser
{
    private const FUNCTION_REGEX = '#^\(([A-z0-9, \$]*?){1}\)[ ]?\=\>[ ]?(.{1,})$#u';
    private const PARAM_REGEX = '#^\$[A-z0-9\_]{1,}$#';
    private const ARGUMENT_SEPARATOR = ',';
    private const ARRAY_FUNCTION_OPERATOR = '=>';

    /** @var string|null */
    private $exceptionClass;

    public function __construct(string $exceptionClass = null)
    {
        if ($exceptionClass !== null) {
            $this->assertExceptionClass($exceptionClass);
            $this->exceptionClass = $exceptionClass;
        }
    }

    private function assertExceptionClass(string $exceptionClass): void
    {
        if (!class_exists($exceptionClass)) {
            throw new \LogicException(sprintf('Given exception class "%s" does not exists.', $exceptionClass));
        }

        if (!array_key_exists(\Throwable::class, class_implements($exceptionClass))) {
            throw new \LogicException('Given exception class must implement Throwable interface');
        }
    }

    /**
     * @param string|callable $function
     */
    public function parseArrowFunction($function): callable
    {
        if (is_callable($function)) {
            return $function;
        }

        if (!is_string($function)) {
            throw $this->createException('Array function has to be string');
        }

        try {
            return $this->parse($function);
        } catch (AssertionFailedException $e) {
            throw $this->createException($e->getMessage());
        }
    }

    private function parse(string $function): callable
    {
        $function = trim($function);
        if (empty($function)) {
            throw $this->createException('Array function has to be not-empty string');
        }

        Assertion::regex($function, self::FUNCTION_REGEX, 'Array function is not in right format');

        $parts = explode(self::ARRAY_FUNCTION_OPERATOR, $function, 2);  // ['($a, $b)', '$a + $b']
        $params = explode(self::ARGUMENT_SEPARATOR, str_replace(['(', ')', ' '], '', $parts[0]));   // ['$a', '$b']

        $this->assertParamsSyntax($params);

        $functionBody = trim(trim($parts[1], '; {}'), '; ');  // '$a + $b'

        $callback = mb_strpos($functionBody, 'return') === false
            ? sprintf('$callback = function(%s){return %s;};', implode(',', $params), $functionBody)
            : sprintf('$callback = function(%s){%s;};', implode(',', $params), $functionBody);

        eval($callback);

        if (is_callable($callback)) {
            return $callback;
        }

        throw $this->createException('Array function is not in right format');
    }

    private function createException(string $message): \Throwable
    {
        $exceptionClass = $this->exceptionClass ?? \InvalidArgumentException::class;

        return new $exceptionClass($message);
    }

    private function assertParamsSyntax(array $params): void
    {
        foreach (array_filter($params) as $param) {
            Assertion::regex($param, self::PARAM_REGEX, 'Params are not in right format');
        }
    }
}
