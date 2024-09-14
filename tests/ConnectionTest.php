<?php
declare(strict_types=1);

namespace Riverside\Orm\Tests;

use PHPUnit\Framework\TestCase;
use Riverside\Orm\Configuration;
use Riverside\Orm\Connection;
use Riverside\Orm\Exception;

class ConnectionTest extends TestCase
{
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        putenv('DEFAULT_USERNAME=root');
        putenv('DEFAULT_PASSWORD=1');
        putenv('DEFAULT_DATABASE=:memory:');
        putenv('DEFAULT_HOST=localhost');
        putenv('DEFAULT_PORT=3306');
        putenv('DEFAULT_DRIVER=sqlite');
        putenv('DEFAULT_CHARSET=utf8mb4');
        putenv('DEFAULT_COLLATION=utf8mb4_general_ci');
    }

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

    /**
     * @return Configuration
     */
    public function testConfigurationWrongCredentials(): Configuration
    {
        $configuration = new Configuration(
            getenv('DEFAULT_USERNAME'),
            getenv('DEFAULT_PASSWORD'),
            getenv('DEFAULT_DATABASE'),
            getenv('DEFAULT_HOST'),
            (int) getenv('DEFAULT_PORT'),
            'wrong_driver',
            getenv('DEFAULT_CHARSET'),
            getenv('DEFAULT_COLLATION')
        );

        $this->assertInstanceOf(Configuration::class, $configuration);

        return $configuration;
    }

    /**
     * @depends testConfiguration
     * @param Configuration $configuration
     * @return Connection
     */
    public function testConnection(Configuration $configuration): Connection
    {
        $connection = new Connection($configuration);

        $this->assertInstanceOf(Connection::class, $connection);

        return $connection;
    }

    /**
     * @depends testConfigurationWrongCredentials
     * @param Configuration $configuration
     * @return Connection
     */
    public function testConnectionWrongCredentials(Configuration $configuration): Connection
    {
        $connection = new Connection($configuration);

        $this->assertInstanceOf(Connection::class, $connection);

        return $connection;
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

    /**
     * @depends testConnection
     * @param Connection $connection
     */
    public function testDependencyInjection(Connection $connection)
    {
        switch (getenv('DEFAULT_DRIVER'))
        {
            case 'mysql':
                $dsn = sprintf('%s:host=%s;port=%u;dbname=%s;charset=%s',
                    getenv('DEFAULT_DRIVER'), getenv('DEFAULT_HOST'), getenv('DEFAULT_PORT'),
                    getenv('DEFAULT_DATABASE'), getenv('DEFAULT_CHARSET'));
                $this->assertSame($dsn, $connection->getDsn());
                break;
            default:
                $this->assertTrue(true);
        }
    }

    /**
     * @depends testConnection
     * @param Connection $connection
     * @throws \Riverside\Orm\Exception
     * @return Connection
     */
    public function testConnectSucceed(Connection $connection): Connection
    {
        $connection->connect();

        $this->assertInstanceOf(\PDO::class, $connection->getDbh());

        return $connection;
    }

    /**
     * @depends testConnectionWrongCredentials
     * @param Connection $connection
     * @throws \Riverside\Orm\Exception
     */
    public function testConnectFailed(Connection $connection)
    {
        $this->expectException(Exception::class);

        $connection->connect();
    }

    /**
     * @depends testConnectSucceed
     * @param Connection $connection
     * @throws \Riverside\Orm\Exception
     */
    public function testReconnectSucceed(Connection $connection)
    {
        $connection->reconnect();

        $this->assertInstanceOf(\PDO::class, $connection->getDbh());
    }

    /**
     * @depends testConnectSucceed
     * @param Connection $connection
     * @return Connection
     */
    public function testDisconnect(Connection $connection): Connection
    {
        $connection->disconnect();

        $this->assertNull($connection->getDbh());

        return $connection;
    }

    /**
     * @depends testConnectionWrongCredentials
     * @param Connection $connection
     * @throws \Riverside\Orm\Exception
     */
    public function testReconnectFailed(Connection $connection)
    {
        $this->expectException(Exception::class);

        $connection->reconnect();
    }

    /**
     * @depends testConnection
     * @param Connection $connection
     */
    public function testDbhFailed(Connection $connection)
    {
        $this->assertNull($connection->getDbh());
    }
}
