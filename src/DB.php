<?php
namespace PhpOrm;

class DB
{
    protected $attributes = array();

    private static $config = 'database.php';

    protected $connection = 'default';

    private $data = null;

    /**
     * @var \PDO
     */
    private $dbh = null;

    private $debug = false;

    private $groupBy = null;

    private $having = null;

    private $join = array();

    private $offset = null;

    private $orderBy = null;

    private $params = array();

    private static $pool = array();

    private $rowCount = null;

    private $select = null;

    /**
     * @var \PDOStatement
     */
    private $sth = null;

    protected $table = null;

    private $where = array();

    /**
     * DB constructor.
     *
     * @throws \Exception
     */
    public function __construct() {
        $this->mount();
    }

    /**
     * @param int $value
     * @return DB
     * @throws \Exception
     */
    public function autoCommit(int $value): DB
    {
        $statement = sprintf("SET AUTOCOMMIT = %u;", $value);
        $this->fire($statement);

        $this->dump();

        return $this;
    }

    /**
     * @return DB
     * @throws \Exception
     */
    public function begin(): DB
    {
        $statement = "BEGIN;";
        $this->fire($statement);

        $this->dump();

        return $this;
    }

    /**
     * @param array $data
     * @param null $modifiers
     * @param null $type
     * @return string
     */
    protected function buildInsert(array $data, $modifiers = null, $type = null): string
    {
        if ($modifiers) {
            $modifiers = is_array($modifiers) ? $modifiers : array($modifiers);
            $modifiers = array_intersect(array('LOW_PRIORITY', 'DELAYED', 'HIGH_PRIORITY', 'IGNORE'), $modifiers);
        }

        $cmd = ($type == 'replace') ? 'REPLACE' : 'INSERT';

        if ($modifiers) {
            $statement = sprintf("%s %s INTO %s", $cmd, join(' ', $modifiers), $this->table);
        } else {
            $statement = sprintf("%s INTO %s", $cmd, $this->table);
        }

        $tmp = array();
        $i = 0;
        foreach ($data as $column => $value) {
            if ($this->attributes && !in_array($column, $this->attributes)) {
                continue;
            }

            if ($value == "") {
                $value = self::raw('NULL');
            }

            if ($value instanceof Expression) {
                $tmp[] = sprintf("%s = %s", $column, $value->getValue());
            } else {
                $tmp[] = sprintf("%s = :set_$i", $column);
                $this->param(":set_$i", $value);
            }
            $i += 1;
        }

        if ($type == 'upsert') {
            $statement .= sprintf(" SET %1\$s ON DUPLICATE KEY UPDATE %1\$s;", join(", ", $tmp));
        } else {
            $statement .= sprintf(" SET %s;", join(", ", $tmp));
        }

        return $statement;
    }

    /**
     * @return string
     */
    protected function buildJoin(): string
    {
        $tmp = array();
        foreach ($this->join as $item) {
            $tmp[] = sprintf("%s JOIN %s ON %s", $item['type'], $item['table'], $item['conditions']);
        }

        return join("\n", $tmp);
    }

    /**
     * @return string
     */
    protected function buildSelect(): string
    {
        $statement = "SELECT " . ($this->select ? $this->select : '*');

        $statement .= "\nFROM " . $this->table;

        if ($this->join) {
            $statement .= "\n" . $this->buildJoin();
        }

        if ($this->where) {
            $statement .= "\nWHERE " . $this->buildWhere();
        }

        if ($this->groupBy) {
            $statement .= "\nGROUP BY " . $this->groupBy;
        }

        if ($this->having) {
            $statement .= "\nHAVING " . $this->having;
        }

        if ($this->orderBy) {
            $statement .= "\nORDER BY " . $this->orderBy;
        }

        if (is_numeric($this->rowCount)) {
            if (is_numeric($this->offset)) {
                $statement .= sprintf("\nLIMIT %u, %u", $this->offset, $this->rowCount);
            } else {
                $statement .= sprintf("\nLIMIT %u", $this->rowCount);
            }
        }

        $statement .= ";";

        return $statement;
    }

    /**
     * @return string
     */
    protected function buildWhere(): string
    {
        $tmp = array();
        $i = 0;
        foreach ($this->where as $item) {
            if ($item['column'] && $item['operator']) {
                $tmp[] = sprintf("%s %s %s",
                    $item['column'],
                    $item['operator'],
                    ":where_$i");
                $this->param(":where_$i", $item['value']);
            } else {
                $tmp[] = $item['value'];
            }

            $i += 1;
        }

        return join(' AND ', $tmp);
    }

    /**
     * @return DB
     * @throws \Exception
     */
    public function commit(): DB
    {
        $statement = "COMMIT;";
        $this->fire($statement);

        $this->dump();

        return $this;
    }

    /**
     * @param string $filename
     */
    public static function config(string $filename) {
        self::$config = $filename;
    }

    /**
     * @return int|null
     * @throws \Exception
     */
    public function count(): ?int
    {
        $statement = "SELECT COUNT(*) AS cnt";

        $statement .= " FROM " . $this->table;

        if ($this->join) {
            $statement .= "JOIN " . $this->join;
        }

        if ($this->where) {
            $statement .= " WHERE " . $this->buildWhere();
        }

        if ($this->groupBy) {
            $statement .= " GROUP BY " . $this->groupBy;
        }

        if ($this->having) {
            $statement .= " HAVING " . $this->having;
        }

        if ($this->groupBy) {
            $statement = sprintf("SELECT COUNT(*) AS cnt FROM (%s) AS tmp", $statement);
        }

        $statement .= " LIMIT 1;";

        $this->fire($statement);

        $this->dump();

        return $this->sth->fetchColumn();
    }

    /**
     * @param bool $value
     * @return DB
     */
    public function debug(bool $value): DB
    {
        $this->debug = $value;

        return $this;
    }

    /**
     * @param null $modifiers
     * @return int|null
     * @throws \Exception
     */
    public function delete($modifiers = null): ?int
    {
        if ($modifiers) {
            $modifiers = is_array($modifiers) ? $modifiers : array($modifiers);
            $modifiers = array_intersect(array('LOW_PRIORITY', 'QUICK', 'IGNORE'), $modifiers);
        }

        if ($modifiers) {
            $statement = sprintf("DELETE %s FROM %s", join(' ', $modifiers), $this->table);
        } else {
            $statement = sprintf("DELETE FROM %s", $this->table);
        }

        if ($this->where) {
            $statement .= " WHERE " . $this->buildWhere();
        }

        if ($this->orderBy) {
            $statement .= " ORDER BY " . $this->orderBy;
        }

        if (is_numeric($this->rowCount)) {
            $statement .= sprintf(" LIMIT %u", $this->rowCount);
        }

        $statement .= ";";

        $this->fire($statement);

        $this->dump();

        return $this->sth->rowCount();
    }

    protected function dump()
    {
        if ($this->debug) {
            echo '<pre>';
            $this->sth->debugDumpParams();
            echo '</pre>';
        }
    }

    /**
     * @param $value
     * @return array|null
     * @throws \Exception
     */
    public function find($value): ?array
    {
        $this->where($this->table . '.id', $value);
        $this->limit(1, 0);

        $this->fire($this->buildSelect());

        $this->dump();

        return $this->sth->fetch(\PDO::FETCH_ASSOC) ?: NULL;
    }

    /**
     * @param string $statement
     * @return bool
     * @throws \Exception
     */
    protected function fire(string $statement): bool
    {
        $this->sth = $this->dbh->prepare($statement);
        if (!$this->sth) {
            throw new \Exception('Preparing statement failed.');
        }

        if (!$this->sth->execute($this->params)) {
            throw new \Exception('Executing statement failed.');
        }

        return true;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function first(): ?array
    {
        $this->fire($this->buildSelect());

        $this->dump();

        return $this->sth->fetch(\PDO::FETCH_ASSOC) ?: NULL;
    }

    /**
     * @return array|null
     * @throws \Exception
     */
    public function get(): ?array
    {
        $this->fire($this->buildSelect());

        $this->dump();

        return $this->sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param string|null $value
     * @return DB
     */
    public function groupBy(string $value = null): DB
    {
        $this->groupBy = $value;

        return $this;
    }

    /**
     * @param string|null $value
     * @return DB
     */
    public function having(string $value = null): DB
    {
        $this->having = $value;

        return $this;
    }

    /**
     * @param array $data
     * @param null $modifiers
     * @return int|null
     * @throws \Exception
     */
    public function insert(array $data, $modifiers = null): ?int
    {
        $this->fire($this->buildInsert($data, $modifiers));

        $this->dump();

        return $this->sth->rowCount()
            ? $this->dbh->lastInsertId()
            : false;
    }

    /**
     * @param string $table
     * @param string $conditions
     * @return DB
     */
    public function join(string $table, string $conditions): DB
    {
        $this->join[] = array(
            'type' => 'INNER',
            'table' => $table,
            'conditions' => $conditions,
        );

        return $this;
    }

    /**
     * @param string $table
     * @param string $conditions
     * @return DB
     */
    public function leftJoin(string $table, string $conditions): DB
    {
        $this->join[] = array(
            'type' => 'LEFT',
            'table' => $table,
            'conditions' => $conditions,
        );

        return $this;
    }

    /**
     * @param int|null $rowCount
     * @param int|null $offset
     * @return DB
     */
    public function limit(int $rowCount = null, int $offset = null): DB
    {
        $this->rowCount = $rowCount;
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return DB
     * @throws \Exception
     */
    protected function mount(): DB
    {
        if (array_key_exists($this->connection, self::$pool)
            && is_object(self::$pool[$this->connection])
            && self::$pool[$this->connection] instanceof \PDO
        ) {
            $this->dbh = self::$pool[$this->connection];
            return $this;
        }

        if (!is_file(self::$config))
        {
            throw new \Exception("File '".self::$config."' not found.");
        }
        $database = include self::$config;
        if (!isset($database[$this->connection]))
        {
            throw new \Exception("Connection not found.");
        }
        $opts = $database[$this->connection];
        foreach (array('username', 'password', 'database', 'host', 'port', 'driver', 'charset', 'collation') as $key)
        {
            if (!array_key_exists($key, $opts))
            {
                throw new \Exception("The '$key' index was not found in config.");
            }
        }

        $configuration = new Configuration(
            $opts['username'],
            $opts['password'],
            $opts['database'],
            $opts['host'],
            $opts['port'],
            $opts['driver'],
            $opts['charset'],
            $opts['collation']);
        $connection = new Connection($configuration);
        $connection->connect();
        $this->dbh = $connection->getDbh();
        self::$pool[$this->connection] = $this->dbh;

        return $this;
    }

    /**
     * @param string|null $value
     * @return DB
     */
    public function orderBy(string $value = null): DB
    {
        $this->orderBy = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param $value
     * @return DB
     */
    public function param(string $key, $value): DB
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return Expression
     */
    public static function raw(string $value): Expression
    {
        return new Expression($value);
    }

    /**
     * @param string $identifier
     * @return DB
     * @throws \Exception
     */
    public function releaseSavepoint(string $identifier): DB
    {
        $statement = sprintf("RELEASE SAVEPOINT %s;", $identifier);
        $this->fire($statement);

        $this->dump();

        return $this;
    }

    /**
     * @param array $data
     * @param null $modifiers
     * @return int|null
     * @throws \Exception
     */
    public function replace(array $data, $modifiers = null): ?int
    {
        $this->fire($this->buildInsert($data, $modifiers, 'replace'));

        $this->dump();

        return $this->sth->rowCount()
            ? $this->dbh->lastInsertId()
            : false;
    }

    /**
     * @return DB
     */
    public function reset(): DB
    {
        $this->data = null;
        $this->debug = false;
        $this->groupBy = null;
        $this->having = null;
        $this->join = array();
        $this->offset = null;
        $this->orderBy = null;
        $this->params = array();
        $this->rowCount = null;
        $this->select = null;
        $this->sth = null;
        $this->where = array();

        return $this;
    }

    /**
     * @param string $table
     * @param string $conditions
     * @return DB
     */
    public function rightJoin(string $table, string $conditions): DB
    {
        $this->join[] = array(
            'type' => 'RIGHT',
            'table' => $table,
            'conditions' => $conditions,
        );

        return $this;
    }

    /**
     * @return DB
     * @throws \Exception
     */
    public function rollback(): DB
    {
        $statement = "ROLLBACK;";
        $this->fire($statement);

        $this->dump();

        return $this;
    }

    /**
     * @param string $identifier
     * @return DB
     * @throws \Exception
     */
    public function rollbackToSavepoint(string $identifier): DB
    {
        $statement = sprintf("ROLLBACK TO SAVEPOINT %s;", $identifier);
        $this->fire($statement);

        $this->dump();

        return $this;
    }

    /**
     * @param string $identifier
     * @return DB
     * @throws \Exception
     */
    public function savepoint(string $identifier): DB
    {
        $statement = sprintf("SAVEPOINT %s;", $identifier);
        $this->fire($statement);

        $this->dump();

        return $this;
    }

    /**
     * @param string|null $value
     * @return DB
     */
    public function select(string $value = null): DB
    {
        $this->select = $value;

        return $this;
    }

    /**
     * @param string $value
     * @return DB
     */
    public function table(string $value): DB
    {
        $this->table = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function truncate(): bool
    {
        $statement = sprintf("TRUNCATE TABLE %s;", $this->table);

        $result = $this->dbh->exec($statement);

        return ($result !== false ? true : false);
    }

    /**
     * @param array $data
     * @param null $modifiers
     * @return int
     * @throws \Exception
     */
    public function update(array $data, $modifiers = null): int
    {
        if ($modifiers) {
            $modifiers = is_array($modifiers) ? $modifiers : array($modifiers);
            $modifiers = array_intersect(array('LOW_PRIORITY', 'IGNORE'), $modifiers);
        }

        if ($modifiers) {
            $statement = sprintf("UPDATE %s %s", join(' ', $modifiers), $this->table);
        } else {
            $statement = sprintf("UPDATE %s", $this->table);
        }

        $tmp = array();
        $i = 0;
        foreach ($data as $column => $value) {
            if ($this->attributes && !in_array($column, $this->attributes)) {
                continue;
            }

            if ($value instanceof Expression) {
                $tmp[] = sprintf("%s = %s", $column, $value->getValue());
            } else {
                $tmp[] = sprintf("%s = :set_$i", $column);
                $this->param(":set_$i", $value != '' ? $value : NULL);
            }
            $i += 1;
        }

        $statement .= " SET " . join(", ", $tmp);

        if ($this->where) {
            $statement .= " WHERE " . $this->buildWhere();
        }

        if ($this->orderBy) {
            $statement .= " ORDER BY " . $this->orderBy;
        }

        if (is_numeric($this->rowCount)) {
            $statement .= sprintf(" LIMIT %u", $this->rowCount);
        }

        $statement .= ";";

        $this->fire($statement);

        $this->dump();

        return $this->sth->rowCount();
    }

    /**
     * @param array $data
     * @param null $modifiers
     * @return int|null
     * @throws \Exception
     */
    public function upsert(array $data, $modifiers = null): ?int
    {
        $this->fire($this->buildInsert($data, $modifiers, 'upsert'));

        return $this->sth->rowCount()
            ? $this->dbh->lastInsertId()
            : false;
    }

    /**
     * @param string $column
     * @return string
     * @throws \Exception
     */
    public function value(string $column): string
    {
        $row = $this->first();
        if (!$row) {
            return false;
        }

        return array_key_exists($column, $row) ? $row[$column] : NULL;
    }

    /**
     * @return DB
     */
    public function where(): DB
    {
        switch (func_num_args()) {
            case 1:
                $column = null;
                $operator = null;
                $value = func_get_arg(0);
                break;
            case 2:
                $column = func_get_arg(0);
                $operator = '=';
                $value = func_get_arg(1);
                break;
            case 3:
                $column = func_get_arg(0);
                $operator = func_get_arg(1);
                $value = func_get_arg(2);
                break;
            default:
                $column = null;
                $operator = null;
                $value = null;
        }

        $this->where[] = array(
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        );

        return $this;
    }
}
