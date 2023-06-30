<?php
namespace PhpOrm;

/**
 * Class Configuration
 *
 * @package PhpOrm
 */
class Configuration
{
    /**
     * Charset
     *
     * @var string
     */
    private $charset;

    /**
     * Collation
     *
     * @var string
     */
    private $collation;

    /**
     * Database name
     *
     * @var string
     */
    private $database;

    /**
     * Driver
     *
     * @var string
     */
    private $driver;

    /**
     * Hostname
     *
     * @var string
     */
    private $host;

    /**
     * Password
     *
     * @var string
     */
    private $password;

    /**
     * Port
     *
     * @var int
     */
    private $port;

    /**
     * Username
     *
     * @var string
     */
    private $username;

    /**
     * Configuration constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $database
     * @param string|null $host
     * @param int|null $port
     * @param string|null $driver
     * @param string|null $charset
     * @param string|null $collation
     */
    public function __construct(string $username,
                                string $password,
                                string $database,
                                string $host=null,
                                int $port=null,
                                string $driver=null,
                                string $charset=null,
                                string $collation=null)
    {
        $this->host     = $host ?: 'localhost';
        $this->port     = $port ?: 3306;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->driver   = $driver ?: 'mysql';
        $this->charset   = $charset ?: 'utf8mb4';
        $this->collation = $collation ?: 'utf8mb4_general_ci';
    }

    /**
     * Get charset
     *
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Get collation
     *
     * @return string
     */
    public function getCollation(): string
    {
        return $this->collation;
    }

    /**
     * Get database name
     *
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * Get driver
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
}
