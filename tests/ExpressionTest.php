<?php
declare(strict_types=1);

namespace Riverside\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Riverside\Orm\Expression;

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
