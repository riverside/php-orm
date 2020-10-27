<?php
namespace PhpOrm;

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
    public function testAttributes()
    {
        $attributes = array(
            'data',
            'dbh',
            'debug',
            'groupBy',
            'having',
            'join',
            'offset',
            'orderBy',
            'pk',
            'rowCount',
            'select',
            'table',
            'where',
        );
        foreach ($attributes as $attribute) {
            $this->assertClassHasAttribute($attribute, DB::class);
        }
    }
}