<?php declare(strict_types=1);

namespace MF\Parser;

use Assert\InvalidArgumentException;
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
     * @param mixed $function
     *
     * @dataProvider invalidFuncProvider
     */
    public function testShouldThrowExceptionWhenArrayFuncIsNotRight($function): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->callbackParser->parseArrowFunction($function);
    }

    public function invalidFuncProvider(): array
    {
        return [
            'not a string' => [0],
            'empty body' => ['() => '],
            'empty body with paramters' => ['($k, $v, $i) =>'],
            'syntax error - invalid variables' => ['(a, b) => a + b'],
            'missing bracers - constant' => ['$a => 2'],
            'missing bracers' => ['$a => $a + 2;'],
            'simple arrow' => ['($a) -> $a + 2;'],
            'named parameter' => ['(SimpleEntity $entity) => {return $entity->getId();}'],
        ];
    }

    /**
     * @param mixed $expected
     *
     * @dataProvider functionProvider
     */
    public function testShouldParseArrayFunction(string $function, array $args, $expected): void
    {
        $callback = $this->callbackParser->parseArrowFunction($function);

        $this->assertInternalType('callable', $callback);
        $this->assertEquals($expected, call_user_func_array($callback, $args));
    }

    public function functionProvider(): array
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
}
