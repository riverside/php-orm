<?php
namespace PhpOrm\Tests;

use PHPUnit\Framework\TestCase;
use PhpOrm\DB;

class DBTest extends TestCase
{
    public function testAttributes()
    {
        $attributes = array(
            'attributes',
            'config',
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
            'pool',
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