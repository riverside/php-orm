<?php
namespace PhpOrm;

class Expression
{
    protected $value;
    
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    
    public function getValue(): string
    {
        return $this->value;
    }
    
    public function __toString(): string
    {
        return (string) $this->value;
    }
}
