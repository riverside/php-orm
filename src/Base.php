<?php
declare(strict_types=1);

namespace Riverside\Orm;

/**
 * Class Base
 *
 * @package Riverside\Orm
 */
class Base
{
    /**
     * Throws an exception
     *
     * @param string $message
     * @param int $code (optional)
     * @param \Throwable|null $previous (optional)
     * @throws Exception
     */
    public function throwException(string $message, int $code=0, \Throwable $previous=null)
    {
        throw new Exception($message, $code, $previous);
    }
}
