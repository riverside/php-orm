<?php
namespace PhpOrm\Tests;

use PHPUnit\Framework\TestCase;
use PhpOrm\Configuration;
use PhpOrm\Connection;

class ConnectionTest extends TestCase
{
    public static function getConfiguration()
    {
        return new Configuration(
            'root',
            'secret',
            'test',
            'localhost',
            3306,
            'mysql',
            'utf8mb4',
            'utf8mb4_general_ci'
        );
    }

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
        $configuration = self::getConfiguration();
        $connection = new Connection($configuration);

        $this->assertSame('mysql:host=localhost;port=3306;dbname=test;charset=utf8mb4', $connection->getDsn());
    }

    public function testConnectFailed()
    {
        $configuration = self::getConfiguration();
        $connection = new Connection($configuration);

        $this->expectException(\Exception::class);

        $connection->connect();
    }

    public function testDisconnect()
    {
        $configuration = self::getConfiguration();
        $connection = new Connection($configuration);

        $connection->disconnect();

        $this->assertNull($connection->getDbh());
    }

    public function testReconnectFailed()
    {
        $configuration = self::getConfiguration();
        $connection = new Connection($configuration);

        $this->expectException(\Exception::class);

        $connection->reconnect();
    }

    public function testDbhFailed()
    {
        $configuration = self::getConfiguration();
        $connection = new Connection($configuration);

        $this->assertNull($connection->getDbh());
    }
}