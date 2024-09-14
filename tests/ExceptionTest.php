<?php
declare(strict_types=1);

namespace Riverside\Orm\Tests;

use Riverside\Orm\Exception;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testExceptionMessage()
    {
        $message = 'Custom exception message';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($message);

        throw new Exception($message);
    }

    /**
     * @throws Exception
     */
    public function testExceptionCode()
    {
        $code = 123;
        $this->expectException(Exception::class);
        $this->expectExceptionCode($code);

        throw new Exception('Custom exception message', $code);
    }

    public function testPreviousException()
    {
        $previousException = new \Exception('Previous exception');
        $exception = new Exception('Custom exception message', 0, $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }
}
