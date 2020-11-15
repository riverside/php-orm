<?php
namespace PhpOrm;

class Configuration
{
    private $charset;

    private $collation;

    private $database;

    private $driver;

    private $host;

    private $password;

    private $port;

    private $username;

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

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function getCollation(): string
    {
        return $this->collation;
    }

    public function getDatabase(): string
    {
        return $this->database;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}