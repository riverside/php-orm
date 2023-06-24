<?php
namespace PhpOrm\Tests;

use PHPUnit\Framework\TestCase;
use PhpOrm\Configuration;

class ConfigurationTest extends TestCase
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
            'charset',
            'collation',
            'database',
            'driver',
            'host',
            'password',
            'port',
            'username',
        );
        foreach ($attributes as $attribute) {
            $this->assertClassHasAttribute($attribute, Configuration::class);
        }
    }

    public function testConfig()
    {
        $configuration = self::getConfiguration();

        $this->assertSame('utf8mb4', $configuration->getCharset());
        $this->assertSame('utf8mb4_general_ci', $configuration->getCollation());
        $this->assertSame('test', $configuration->getDatabase());
        $this->assertSame(3306, $configuration->getPort());
        $this->assertSame('localhost', $configuration->getHost());
        $this->assertSame('root', $configuration->getUsername());
        $this->assertSame('secret', $configuration->getPassword());
        $this->assertSame('mysql', $configuration->getDriver());
    }
}