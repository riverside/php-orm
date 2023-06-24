<?php
namespace PhpOrm\Tests;

use PHPUnit\Framework\TestCase;
use PhpOrm\DB;

class DBTest extends TestCase
{
    public static function init()
    {
        $data = '<?php return array("default" => array(
            "username" => "root",
            "password" => "secret",
            "database" => "test",
            "host" => "localhost",
            "port" => 3306,
            "driver" => "mysql",
            "charset" => "utf8mb4",
            "collation" => "utf8mb4_general_ci",
        ));';
        $filename = tempnam(sys_get_temp_dir(), 'Tux');
        file_put_contents($filename, $data, LOCK_EX);
        DB::config($filename);
    }

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

    public function testConstruct()
    {
        $this->expectException(\Exception::class);

        self::init();
        new DB();
    }
}