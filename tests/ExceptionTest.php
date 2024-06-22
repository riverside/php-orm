<?php
namespace PhpOrm\Tests;

use PhpOrm\Exception;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testExceptionMessage()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Custom exception message');

        throw new Exception('Custom exception message');
    }

    public function testExceptionCode()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(123);

        throw new Exception('Custom exception message', 123);
    }

    public function testPreviousException()
    {
        $previousException = new \Exception('Previous exception');
        $exception = new Exception('Custom exception message', 0, $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }
}