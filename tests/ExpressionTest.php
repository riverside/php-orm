<?php
namespace PhpOrm\Tests;

use PHPUnit\Framework\TestCase;
use PhpOrm\Expression;

class ExpressionTest extends TestCase
{
    public function testAttributes()
    {
        $attributes = array(
            'value',
        );
        foreach ($attributes as $attribute) {
            $this->assertClassHasAttribute($attribute, Expression::class);
        }
    }

    public function testString()
    {
        $stub = $this->createMock(Expression::class);
        $stub->method('__toString')->willReturn('string');
        $this->assertEquals('string', (string) $stub);
    }
}