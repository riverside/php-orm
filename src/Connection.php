<?php
namespace PhpOrm;

class Connection
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var \PDO|null
     */
    private $dbh;

    /**
     * Connection constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return \PDO|null
     * @throws \Exception
     */
    public function getDbh()
    {
        return $this->dbh;
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        $dsn = "";
        switch ($this->configuration->getDriver())
        {
            case 'mysql':
                $dsn = sprintf('mysql:host=%s;port=%u;dbname=%s;charset=%s',
                    $this->configuration->getHost(),
                    $this->configuration->getPort(),
                    $this->configuration->getDatabase(),
                    $this->configuration->getCharset());
                break;
            case 'oci':
                $dsn = sprintf('oci:dbname=%s;charset=%s',
                    $this->configuration->getDatabase(),
                    $this->configuration->getCharset());
                break;
            case 'firebird':
                $dsn = sprintf('firebird:dbname=%s;charset=%s',
                    $this->configuration->getDatabase(),
                    $this->configuration->getCharset());
                break;
            case 'pgsql':
                $dsn = sprintf('pgsql:host=%s;port=%u;dbname=%s;user=%s;password=%s',
                    $this->configuration->getHost(),
                    $this->configuration->getPort(),
                    $this->configuration->getDatabase(),
                    $this->configuration->getUsername(),
                    $this->configuration->getPassword());
                break;
            case 'sqlite':
                $dsn = sprintf('sqlite:%s',
                    $this->configuration->getDatabase());
                break;
            case 'sybase':
            case 'mssql':
            case 'dblib':
                $dsn = sprintf("%s:host=%s;dbname=%s;charset=%s",
                    $this->configuration->getDriver(),
                    $this->configuration->getHost(),
                    $this->configuration->getDatabase(),
                    $this->configuration->getCharset());
                break;
            case 'cubrid':
                $dsn = sprintf("cubrid:host=%s;port=%u;dbname=%s",
                    $this->configuration->getHost(),
                    $this->configuration->getPort(),
                    $this->configuration->getDatabase());
                break;
            case '4D':
                $dsn = sprintf('4D:host=%s;port=%u;user=%s;password=%s;dbname=%s;charset=%s',
                    $this->configuration->getHost(),
                    $this->configuration->getPort(),
                    $this->configuration->getUsername(),
                    $this->configuration->getPassword(),
                    $this->configuration->getDatabase(),
                    $this->configuration->getCharset());
                break;
        }

        return $dsn;
    }

    /**
     * @return Connection
     * @throws \Exception
     */
    public function connect(): Connection
    {
        try {
            $this->dbh = new \PDO($this->getDsn(), $this->configuration->getUsername(), $this->configuration->getPassword());
            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES,true);

        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return $this;
    }

    /**
     * @return Connection
     */
    public function disconnect(): Connection
    {
        $this->dbh = null;

        return $this;
    }

    /**
     * @return Connection
     * @throws \Exception
     */
    public function reconnect(): Connection
    {
        $this->disconnect();
        $this->connect();

        return $this;
    }
}