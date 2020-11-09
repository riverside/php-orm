<?php
namespace PhpOrm;

class DB
{
    protected $attributes = array();

    private static $connection = null;

    private $data = null;
    
    private $dbh = null;
    
    private $debug = false;
    
    private $groupBy = null;
    
    private $having = null;
    
    private $join = array();
        
    private $offset = null;
    
    private $orderBy = null;
    
    private $params = array();

    private $rowCount = null;
    
    private $select = null;
    
    private $sth = null;
    
    protected $table = null;
    
    private $where = array();
    
    public function __construct()
    {
        $this->connect();
    }

    protected function buildInsert(array $data, $modifiers=null, $upsert=false): string
    {
        if ($modifiers)
        {
            $modifiers = is_array($modifiers) ? $modifiers : array($modifiers);
            $modifiers = array_intersect(array('LOW_PRIORITY', 'DELAYED', 'HIGH_PRIORITY', 'IGNORE'), $modifiers);
        }

        if ($modifiers)
        {
            $statement = sprintf("INSERT %s INTO %s", join(' ', $modifiers), $this->table);
        } else {
            $statement = sprintf("INSERT INTO %s", $this->table);
        }

        $tmp = array();
        $i = 0;
        foreach ($data as $column => $value)
        {
            if ($this->attributes && !in_array($column, $this->attributes))
            {
                continue;
            }

            if ($value instanceof Expression)
            {
                $tmp[] = sprintf("%s = %s", $column, $value->getValue());
            } else {
                $tmp[] = sprintf("%s = :set_$i", $column);
                $this->param(":set_$i", $value);
            }
            $i += 1;
        }

        if ($upsert)
        {
            $statement .= sprintf(" SET %1\$s ON DUPLICATE KEY UPDATE %1\$s;", join(", ", $tmp));
        } else {
            $statement .= sprintf(" SET %s;", join(", ", $tmp));
        }

        return $statement;
    }

    protected function buildJoin(): string
    {
        $tmp = array();
        foreach ($this->join as $item)
        {
            $tmp[] = sprintf("%s JOIN %s ON %s", $item['type'], $item['table'], $item['conditions']);
        }
        
        return join("\n", $tmp);
    }
    
    protected function buildSelect(): string
    {
        $statement = "SELECT " . ($this->select ? $this->select : '*');
        
        $statement .= "\nFROM " . $this->table;
        
        if ($this->join)
        {
            $statement .= "\n" . $this->buildJoin();
        }
        
        if ($this->where)
        {
            $statement .= "\nWHERE " . $this->buildWhere();
        }
        
        if ($this->groupBy)
        {
            $statement .= "\nGROUP BY " . $this->groupBy;
        }
        
        if ($this->having)
        {
            $statement .= "\nHAVING " . $this->having;
        }
        
        if ($this->orderBy)
        {
            $statement .= "\nORDER BY " . $this->orderBy;
        }
        
        if (is_numeric($this->rowCount))
        {
            if (is_numeric($this->offset))
            {
                $statement .= sprintf("\nLIMIT %u, %u", $this->offset, $this->rowCount);
            } else {
                $statement .= sprintf("\nLIMIT %u", $this->rowCount);
            }
        }

        $statement .= ";";
        
        return $statement;
    }
    
    protected function buildWhere(): string
    {
        $tmp = array();
        $i = 0;
        foreach ($this->where as $item)
        {
            if ($item['column'] && $item['operator'])
            {
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
    
    protected function connect(): void
    {
        if (is_object(self::$connection))
        {
            $this->dbh = self::$connection;
            return;
        }

        $dsn      = $_ENV['PHP_ORM_DSN'];
        $user     = $_ENV['PHP_ORM_USER'];
        $password = $_ENV['PHP_ORM_PSWD'];
        
        try {
            $this->dbh = new \PDO($dsn, $user, $password);
            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES,true);
            
            self::$connection = $this->dbh;

        } catch (\PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }
    
    public function count(): ?int
    {
        $statement = "SELECT COUNT(*) AS cnt";
        
        $statement .= " FROM " . $this->table;
        
        if ($this->join)
        {
            $statement .= "JOIN " . $this->join;
        }
        
        if ($this->where)
        {
            $statement .= " WHERE " . $this->buildWhere();
        }
        
        if ($this->groupBy)
        {
            $statement .= " GROUP BY " . $this->groupBy;
        }
        
        if ($this->having)
        {
            $statement .= " HAVING " . $this->having;
        }
        
        if ($this->groupBy)
        {
            $statement = sprintf("SELECT COUNT(*) AS cnt FROM (%s) AS tmp", $statement);
        }
        
        $statement .= " LIMIT 1;";
        
        if (!$this->fire($statement))
        {
            return false;
        }
        
        $this->dump();
        
        return $this->sth->fetchColumn();
    }
    
    public function debug(bool $value): DB
    {
        $this->debug = $value;
        
        return $this;
    }
    
    public function delete($modifiers=null): ?int
    {
        if ($modifiers)
        {
            $modifiers = is_array($modifiers) ? $modifiers : array($modifiers);
            $modifiers = array_intersect(array('LOW_PRIORITY', 'QUICK', 'IGNORE'), $modifiers);
        }
        
        if ($modifiers)
        {
            $statement = sprintf("DELETE %s FROM %s", join(' ', $modifiers), $this->table);
        } else {
            $statement = sprintf("DELETE FROM %s", $this->table);
        }
        
        if ($this->where)
        {
            $statement .= " WHERE " . $this->buildWhere();
        }
        
        if ($this->orderBy)
        {
            $statement .= " ORDER BY " . $this->orderBy;
        }
            
        if (is_numeric($this->rowCount))
        {
            $statement .= sprintf(" LIMIT %u", $this->rowCount);
        }
        
        $statement .= ";";

        if (!$this->fire($statement))
        {
            return false;
        }
        
        $this->dump();
        
        return $this->sth->rowCount();
    }

    protected function dump()
    {
        if ($this->debug)
        {
            echo '<pre>';
            $this->sth->debugDumpParams();
            echo '</pre>';
        }
    }

    public function find($value): ?array
    {
        $this->where($this->table . '.id', $value);
        $this->limit(1, 0);
        
        if (!$this->fire($this->buildSelect()))
        {
            return false;
        }
        
        $this->dump();
        
        return $this->sth->fetch(\PDO::FETCH_ASSOC) ? : NULL;
    }

    protected function fire(string $statement): bool
    {
        $this->sth = $this->dbh->prepare($statement);
        if (!$this->sth)
        {
            return false;
        }
        
        if (!$this->sth->execute($this->params))
        {
            return false;
        }
        
        return true;
    }
    
    public function first(): ?array
    {
        if (!$this->fire($this->buildSelect()))
        {
            return false;
        }
        
        $this->dump();
        
        return $this->sth->fetch(\PDO::FETCH_ASSOC) ? : NULL;
    }
    
    public function get(): ?array
    {
        if (!$this->fire($this->buildSelect()))
        {
            return false;
        }
        
        $this->dump();
        
        return $this->sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function groupBy(string $value=null): DB
    {
        $this->groupBy = $value;
        
        return $this;
    }
    
    public function having(string $value=null): DB
    {
        $this->having = $value;
        
        return $this;
    }

    public function insert(array $data, $modifiers=null): ?int
    {
        if (!$this->fire($this->buildInsert($data, $modifiers)))
        {
            return false;
        }
        
        $this->dump();
        
        return $this->sth->rowCount()
            ? $this->dbh->lastInsertId()
            : false;
    }
    
    public function join(string $table, string $conditions): DB
    {
        $this->join[] = array(
            'type' => 'INNER',
            'table' => $table,
            'conditions' => $conditions,
        );
        
        return $this;
    }
    
    public function leftJoin(string $table, string $conditions): DB
    {
        $this->join[] = array(
            'type' => 'LEFT',
            'table' => $table,
            'conditions' => $conditions,
        );
        
        return $this;
    }
    
    public function rightJoin(string $table, string $conditions): DB
    {
        $this->join[] = array(
            'type' => 'RIGHT',
            'table' => $table,
            'conditions' => $conditions,
        );
        
        return $this;
    }
    
    public function limit(int $rowCount=null, int $offset=null): DB
    {
        $this->rowCount = $rowCount;
        $this->offset = $offset;
        
        return $this;
    }
    
    public function orderBy(string $value=null): DB
    {
        $this->orderBy = $value;
        
        return $this;
    }
    
    public function param(string $key, $value): DB
    {
        $this->params[$key] = $value;
        
        return $this;
    }

    public static function raw(string $value): Expression
    {
        return new Expression($value);
    }
    
    public function reset(): DB
    {
        $this->data     = null;
        $this->debug    = false;
        $this->groupBy  = null;
        $this->having   = null;
        $this->join     = array();
        $this->offset   = null;
        $this->orderBy  = null;
        $this->params   = array();
        $this->rowCount = null;
        $this->select   = null;
        $this->sth      = null;
        $this->where    = array();
        
        return $this;
    }
    
    public function select(string $value=null): DB
    {
        $this->select = $value;
        
        return $this;
    }
    
    public function table(string $value): DB
    {
        $this->table = $value;
        
        return $this;
    }
    
    public function truncate(): bool
    {
        $statement = sprintf("TRUNCATE TABLE %s;", $this->table);
        
        $result = $this->dbh->exec($statement);
        
        return ($result !== false ? true : false);
    }
    
    public function update(array $data, $modifiers=null): int
    {
        if ($modifiers)
        {
            $modifiers = is_array($modifiers) ? $modifiers : array($modifiers);
            $modifiers = array_intersect(array('LOW_PRIORITY', 'IGNORE'), $modifiers);
        }
        
        if ($modifiers)
        {
            $statement = sprintf("UPDATE %s %s", join(' ', $modifiers), $this->table);
        } else {
            $statement = sprintf("UPDATE %s", $this->table);
        }
        
        $tmp = array();
        $i = 0;
        foreach ($data as $column => $value)
        {
            if ($this->attributes && !in_array($column, $this->attributes))
            {
                continue;
            }

            if ($value instanceof Expression)
            {
                $tmp[] = sprintf("%s = %s", $column, $value->getValue());
            } else {
                $tmp[] = sprintf("%s = :set_$i", $column);
                $this->param(":set_$i", $value != '' ? $value : NULL);
            }
            $i += 1;
        }
        
        $statement .= " SET " . join(", ", $tmp);
        
        if ($this->where)
        {
            $statement .= " WHERE " . $this->buildWhere();
        }
        
        if ($this->orderBy)
        {
            $statement .= " ORDER BY " . $this->orderBy;
        }
        
        if (is_numeric($this->rowCount))
        {
            $statement .= sprintf(" LIMIT %u", $this->rowCount);
        }

        $statement .= ";";
        
        if (!$this->fire($statement))
        {
            return false;
        }
        
        $this->dump();
        
        return $this->sth->rowCount();
    }

    public function upsert(array $data, $modifiers=null): ?int
    {
        if (!$this->fire($this->buildInsert($data, $modifiers, true)))
        {
            return false;
        }

        return $this->sth->rowCount()
            ? $this->dbh->lastInsertId()
            : false;
    }
        
    public function value(string $column): string
    {
        $row = $this->first();
        if (!$row)
        {
            return false;
        }

        return array_key_exists($column, $row) ? $row[$column] : NULL;
    }

    public function where(): DB
    {
        switch (func_num_args())
        {
            case 1:
                $column   = null;
                $operator = null;
                $value    = func_get_arg(0);
                break;
            case 2:
                $column   = func_get_arg(0);
                $operator = '=';
                $value    = func_get_arg(1);
                break;
            case 3:
                $column   = func_get_arg(0);
                $operator = func_get_arg(1);
                $value    = func_get_arg(2);
                break;
            default:
                $column   = null;
                $operator = null;
                $value    = null;
        }
        
        $this->where[] = array(
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
        );
        
        return $this;
    }
}
