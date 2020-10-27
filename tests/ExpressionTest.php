<?php
namespace PhpOrm;

use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    public function testString()
    {
        $expression = new Expression('NOW()');

        $this->assertIsString($expression);
    }
}