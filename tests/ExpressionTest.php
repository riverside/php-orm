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
        $expression = new Expression('string');
        $this->assertEquals('string', (string) $expression);
    }
}