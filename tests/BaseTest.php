<?php
declare(strict_types=1);

namespace Riverside\Orm\Tests;

use Riverside\Orm\Base;
use Riverside\Orm\Exception;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testException()
    {
        $this->expectException(Exception::class);
        $base = new Base();
        $base->throwException('Text exception');
    }
}
