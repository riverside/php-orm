<?php
namespace PhpOrm;

use PHPUnit\Framework\TestCase;

class ConnectionTest extends TestCase
{
    public function testAttributes()
    {
        $attributes = array(
            'configuration',
            'dbh',
        );
        foreach ($attributes as $attribute) {
            $this->assertClassHasAttribute($attribute, Connection::class);
        }
    }

    public function testDependencyInjection()
    {
        $configuration = new Configuration(
            'root',
            'secret',
            'test',
            'localhost',
            3306,
            'mysql',
            'utf8mb4',
            'utf8mb4_general_ci'
        );
        $connection = new Connection($configuration);

        $this->assertSame('mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4', $connection->getDsn());
    }
}