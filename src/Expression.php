<?php
declare(strict_types=1);

namespace Riverside\Orm;

/**
 * Class Expression
 *
 * @package Riverside\Orm
 */
class Expression
{
    /**
     * @var string
     */
    protected $value;

    /**
     * Expression constructor.
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
