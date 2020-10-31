<?php
namespace PhpOrm;

use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    public function testString()
    {
        $stub = $this->createMock(Expression::class);
        $stub->method('__toString')->willReturn('string');
        $this->assertEquals('string', (string) $stub);
    }
}