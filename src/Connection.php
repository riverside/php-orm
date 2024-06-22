<?php
namespace PhpOrm;

/**
 * Class Connection
 *
 * @package PhpOrm
 */
class Connection extends Base
{
    /**
     * Configuration instance
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * PDO instance
     *
     * @var \PDO|null
     */
    private $dbh;

    /**
     * Connection constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Gets PDO instance
     *
     * @return \PDO|null
     */
    public function getDbh()
    {
        return $this->dbh;
    }

    /**
     * Gets the Data Source Name (DSN)
     *
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
     * Create a PDO instance
     *
     * @return Connection
     * @throws Exception
     */
    public function connect(): Connection
    {
        try {
            $this->dbh = new \PDO($this->getDsn(), $this->configuration->getUsername(), $this->configuration->getPassword());
            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES,true);

        } catch (\PDOException $e) {
            $this->throwException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        return $this;
    }

    /**
     * Destroy the PDO instance
     *
     * @return Connection
     */
    public function disconnect(): Connection
    {
        $this->dbh = null;

        return $this;
    }

    /**
     * Destroys, then creates a new PDO instance
     *
     * @return Connection
     * @throws Exception
     */
    public function reconnect(): Connection
    {
        $this->disconnect();
        $this->connect();

        return $this;
    }
}