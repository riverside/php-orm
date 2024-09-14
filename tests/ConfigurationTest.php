<?php
declare(strict_types=1);

namespace Riverside\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Riverside\Orm\Configuration;

class ConfigurationTest extends TestCase
{
    /**
     * @return Configuration
     */
    public function testConfiguration(): Configuration
    {
        $configuration = new Configuration(
            getenv('DEFAULT_USERNAME'),
            getenv('DEFAULT_PASSWORD'),
            getenv('DEFAULT_DATABASE'),
            getenv('DEFAULT_HOST'),
            (int) getenv('DEFAULT_PORT'),
            getenv('DEFAULT_DRIVER'),
            getenv('DEFAULT_CHARSET'),
            getenv('DEFAULT_COLLATION')
        );

        $this->assertInstanceOf(Configuration::class, $configuration);

        return $configuration;
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

    /**
     * @depends testConfiguration
     * @param Configuration $configuration
     */
    public function testConfig(Configuration $configuration)
    {
        $this->assertSame(getenv('DEFAULT_CHARSET'), $configuration->getCharset());
        $this->assertSame(getenv('DEFAULT_COLLATION'), $configuration->getCollation());
        $this->assertSame(getenv('DEFAULT_DATABASE'), $configuration->getDatabase());
        $this->assertSame((int) getenv('DEFAULT_PORT'), $configuration->getPort());
        $this->assertSame(getenv('DEFAULT_HOST'), $configuration->getHost());
        $this->assertSame(getenv('DEFAULT_USERNAME'), $configuration->getUsername());
        $this->assertSame(getenv('DEFAULT_PASSWORD'), $configuration->getPassword());
        $this->assertSame(getenv('DEFAULT_DRIVER'), $configuration->getDriver());
    }
}
