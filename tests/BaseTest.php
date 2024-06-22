<?php
namespace PhpOrm\Tests;

use PhpOrm\Base;
use PhpOrm\Exception;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    public function testException()
    {
        $this->expectException(Exception::class);
        $base = new Base();
        $base->throwException('Text exception');
    }
}