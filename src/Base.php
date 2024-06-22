<?php
namespace PhpOrm;

/**
 * Class Base
 *
 * @package PhpOrm
 */
class Base
{
    /**
     * Throws an exception
     *
     * @param string $message
     * @param int|null $code
     * @param \Throwable|null $previous
     * @throws Exception
     */
    public function throwException(string $message, int $code=null, \Throwable $previous=null)
    {
        throw new Exception($message, $code, $previous);
    }
}