<?php
namespace PhpOrm;

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
    public function testAttributes()
    {
        $attributes = array(
            'attributes',
            'connection',
            'data',
            'dbh',
            'debug',
            'groupBy',
            'having',
            'join',
            'offset',
            'orderBy',
            'params',
            'rowCount',
            'select',
            'sth',
            'table',
            'where',
        );
        foreach ($attributes as $attribute) {
            $this->assertClassHasAttribute($attribute, DB::class);
        }
    }
}