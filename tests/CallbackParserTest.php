<?php declare(strict_types=1);

namespace MF\Parser;

use MF\Parser\Fixtures\CustomException;
use MF\Parser\Fixtures\Functions;
use MF\Parser\Fixtures\SimpleEntity;
use PHPUnit\Framework\TestCase;

class CallbackParserTest extends TestCase
{
    /** @var CallbackParser */
    private $callbackParser;

    protected function setUp(): void
    {
        $this->callbackParser = new CallbackParser();
    }

    /**
     * @param mixed $function invalid arrow function
     *
     * @dataProvider provideInvalidFunction
     */
    public function testShouldThrowExceptionWhenArrayFuncIsNotRight($function, string $exceptionMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->callbackParser->parseArrowFunction($function);
    }

    public function provideInvalidFunction(): array
    {
        return [
            'not a string' => [0, 'Array function has to be string'],
            'empty string' => ['', 'Array function has to be not-empty string'],
            'empty body' => ['() => ', 'Array function is not in right format'],
            'empty body with paramters' => ['($k, $v, $i) =>', 'Array function is not in right format'],
            'syntax error - invalid variables' => ['(a, b) => a + b', 'Params are not in right format'],
            'missing bracers - constant' => ['$a => 2', 'Array function is not in right format'],
            'missing bracers' => ['$a => $a + 2;', 'Array function is not in right format'],
            'simple arrow' => ['($a) -> $a + 2;', 'Array function is not in right format'],
            'named parameter' => [
                '(SimpleEntity $entity) => {return $entity->getId();}',
                'Params are not in right format',
            ],
        ];
    }

    /**
     * @param mixed $expected result of arrow function
     *
     * @dataProvider provideFunction
     */
    public function testShouldParseArrayFunction(string $function, array $args, $expected): void
    {
        $callback = $this->callbackParser->parseArrowFunction($function);

        $this->assertInternalType('callable', $callback);
        $this->assertEquals($expected, call_user_func_array($callback, $args));
    }

    public function provideFunction(): array
    {
        return [
            [
                'function' => '($k, $v) => $k . $v',
                'args' => ['key', 'value'],
                'expected' => 'keyvalue',
            ],
            [
                'function' => '($k, $v) => $k',
                'args' => ['key', 'value'],
                'expected' => 'key',
            ],
            [
                'function' => '($k, $v) => return $v * 2;',
                'args' => ['key', 2],
                'expected' => 4,
            ],
            [
                'function' => '($k, $v, $i) => ($k + $v) * $i;',
                'args' => [2, 3, 4],
                'expected' => 20,
            ],
            [
                'function' => '($k, $v) => $k > 2',
                'args' => [2, 'x'],
                'expected' => false,
            ],
            [
                'function' => '($k) => $k <= 2;',
                'args' => [2],
                'expected' => true,
            ],
            [
                'function' => '() => true',
                'args' => [],
                'expected' => true,
            ],
            [
                'function' => '() => {}',
                'args' => [],
                'expected' => null,
            ],
            [
                'function' => '($x) => {return $x;}',
                'args' => ['x'],
                'expected' => 'x',
            ],
            [
                'function' => '($x, $y) => {return $x;}',
                'args' => ['x', 'y'],
                'expected' => 'x',
            ],
            [
                'function' => '($entity) => {return $entity->getId();}',
                'args' => [new SimpleEntity(10)],
                'expected' => 10,
            ],
        ];
    }

    public function testShouldReturnCallableCallbackRightAway(): void
    {
        $callable = function ($a) {
            return $a;
        };

        $callback = $this->callbackParser->parseArrowFunction($callable);

        $this->assertInternalType('callable', $callback);
        $this->assertEquals($callable, $callback);
    }

    /** @dataProvider provideFqdn */
    public function testShouldNotParseArrayFunctionWhenFunctionByFQDNIsGiven(
        string $functionFQDN,
        string $value,
        string $expected
    ): void {
        $function = $this->callbackParser->parseArrowFunction($functionFQDN);
        $this->assertInternalType('callable', $function);

        $result = $function($value);
        $this->assertSame($expected, $result);
    }

    public function provideFqdn(): array
    {
        return [
            // function, value, expected
            'mb_strtolower' => ['mb_strtolower', 'Hello World', 'hello world'],
            'hello' => [Functions::hello, 'World', 'Hello World!'],
        ];
    }

    /** @dataProvider provideInvalidFunction */
    public function testShouldThrowCustomException($function, string $exceptionMessage): void
    {
        $parser = new CallbackParser(CustomException::class);

        $this->expectException(CustomException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $parser->parseArrowFunction($function);
    }

    /** @dataProvider provideInvalidExceptionClass */
    public function testShouldNotCreateCallbackParserWithInvalidCustomException(
        string $invalidException,
        string $expectedMessage
    ): void {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($expectedMessage);

        new CallbackParser($invalidException);
    }

    public function provideInvalidExceptionClass(): array
    {
        return [
            // invalidException, expectedMessage
            'not a class' => ['Just some string', 'Given exception class "Just some string" does not exists.'],
            'not implements Throwable interface' => [
                SimpleEntity::class,
                'Given exception class must implement Throwable interface',
            ],
        ];
    }
}
