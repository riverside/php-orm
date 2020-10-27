<?php
namespace PhpOrm;

class DB
{
    private $data = null;
    
    private $dbh = null;
    
    private $debug = false;
    
    private $groupBy = null;
    
    private $having = null;
    
    private $join = array();
        
    private $offset = null;
    
    private $orderBy = null;
    
    protected $pk = null;
    
    private $rowCount = null;
    
    private $select = null;
    
    protected $table = null;
    
    private $where = array();
    
    public function __construct()
    {
        $this->connect();
    }
    
    protected function buildJoin()
    {
        $tmp = array();
        foreach ($this->join as $item)
        {
            $tmp[] = sprintf("%s JOIN %s ON %s", $item['type'], $item['table'], $item['conditions']);
        }
        
        return join(" ", $tmp);
    }
    
    protected function buildSelect()
    {
        $statement = "SELECT " . ($this->select ? $this->select : '*');
        
        $statement .= " FROM " . $this->table;
        
        if ($this->join)
        {
            $statement .= " " . $this->buildJoin();
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
        
        if ($this->orderBy)
        {
            $statement .= " ORDER BY " . $this->orderBy;
        }
        
        if (is_numeric($this->rowCount))
        {
            if (is_numeric($this->offset))
            {
                $statement .= sprintf(" LIMIT %u, %u;", $this->offset, $this->rowCount);
            } else {
                $statement .= sprintf(" LIMIT %u;", $this->rowCount);
            }
        }
        
        return $statement;
    }
    
    protected function buildWhere()
    {
        $tmp = array();
        foreach ($this->where as $item)
        {
            if ($item['column'] && $item['operator'])
            {
                $tmp[] = sprintf("%s %s %s",
                    $item['column'],
                    $item['operator'],
                    $item['value'] instanceof Expression
                        ? $item['value']
                        : "'" . $item['value'] . "'");
            } else {
                $tmp[] = $item['value'];
            }            
        }
        
        return join(' AND ', $tmp);
    }
    
    protected function connect()
    {
        $dsn      = 'mysql:host=localhost;dbname=lurk';
        $user     = 'root';
        $password = '1';
        
        try {
            $this->dbh = new \PDO($dsn, $user, $password);
            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
        } catch (\PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }
    
    public function count()
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
        
        if ($this->debug)
        {
            self::log($statement);
        }
        
        $sth = $this->dbh->query($statement);
        if (!$sth)
        {
            return false;
        }
        
        return $sth->fetchColumn();
    }
    
    public function debug($value=null)
    {
        $this->debug = $value;
        
        return $this;
    }
    
    public function delete($modifiers=null)
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
            $statement .= sprintf(" LIMIT %u;", $this->rowCount);
        }
        
        if ($this->debug)
        {
            self::log($statement);
        }
        
        $sth = $this->dbh->query($statement);
        if (!$sth)
        {
            return false;
        }
        
        return $sth->rowCount();
    }
      
    public function find($value)
    {
        $this->where(sprintf("%s = '%s'", $this->pk, $value));
        $this->limit(1, 0);
        
        $statement = $this->buildSelect();
        
        if ($this->debug)
        {
            self::log($statement);
        }
        
        $sth = $this->dbh->query($statement);
        if (!$sth)
        {
            return false;
        }
        
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function first()
    {
        $statement = $this->buildSelect();
        
        if ($this->debug)
        {
            self::log($statement);
        }
        
        $sth = $this->dbh->query($statement);
        if (!$sth)
        {
            return false;
        }
        
        return $sth->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function get()
    {
        $statement = $this->buildSelect();
        
        if ($this->debug)
        {
            self::log($statement);
        }
        
        $sth = $this->dbh->query($statement);
        if (!$sth)
        {
            return false;
        }
        
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function groupBy($value=null)
    {
        $this->groupBy = $value;
        
        return $this;
    }
    
    public function having($value=null)
    {
        $this->having = $value;
        
        return $this;
    }

    public function insert($data, $modifiers=null)
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
        $params = array();
        $i = 0;
        foreach ($data as $column => $value)
        {
            if ($value instanceof Expression)
            {
                $tmp[] = sprintf("%s = %s", $column, $value);
            } else {
                $tmp[] = sprintf("%s = :param_$i", $column);
                $params[":param_$i"] = $value;
            }
            $i += 1;
        }
        
        $statement .= " SET " . join(", ", $tmp);
        
        $sth = $this->dbh->prepare($statement);
        if (!$sth)
        {
            return false;
        }
        
        if (!$sth->execute($params))
        {
            return false;
        }
        
        return $sth->rowCount()
            ? $this->dbh->lastInsertId()
            : false;
    }
    
    public function join($table, $conditions)
    {
        $this->join[] = array(
            'type' => 'INNER',
            'table' => $table,
            'conditions' => $conditions,
        );
        
        return $this;
    }
    
    public function leftJoin($table, $conditions)
    {
        $this->join[] = array(
            'type' => 'LEFT',
            'table' => $table,
            'conditions' => $conditions,
        );
        
        return $this;
    }
    
    public function rightJoin($table, $conditions)
    {
        $this->join[] = array(
            'type' => 'RIGHT',
            'table' => $table,
            'conditions' => $conditions,
        );
        
        return $this;
    }
    
    public function limit($rowCount=null, $offset=null)
    {
        $this->rowCount = $rowCount;
        $this->offset = $offset;
        
        return $this;
    }
    
    protected static function log($value)
    {
        echo '<pre style="background: #eee; border: solid 1px #ccc; padding: 5px 7px; border-radius: 4px; white-space: normal; word-break: break-word;">';
        echo $value;
        echo '</pre>';
    }
    
    public function orderBy($value=null)
    {
        $this->orderBy = $value;
        
        return $this;
    }

    public static function raw($value)
    {
        return new Expression($value);
    }
    
    public function reset()
    {
        $this->data     = null;
        $this->debug    = false;
        $this->groupBy  = null;
        $this->having   = null;
        $this->join     = array();
        $this->offset   = null;
        $this->orderBy  = null;
        $this->rowCount = null;
        $this->select   = null;
        $this->where    = array();
        
        return $this;
    }
    
    public function select($value=null)
    {
        $this->select = $value;
        
        return $this;
    }
    
    public static function table($value)
    {
        $inst = new self();
        
        $inst->table = $value;
        
        return $inst;
    }
    
    public function truncate()
    {
        $statement = sprintf("TRUNCATE TABLE %s;", $this->table);
        
        $sth = $this->dbh->query($statement);
        if (!$sth)
        {
            return false;
        }
        
        return $this;
    }
    
    public function update($data, $modifiers=null)
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
        $params = array();
        $i = 0;
        foreach ($data as $column => $value)
        {
            if ($value instanceof Expression)
            {
                $tmp[] = sprintf("%s = %s", $column, $value);
            } else {
                $tmp[] = sprintf("%s = :param_$i", $column);
                $params[":param_$i"] = $value;
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
            $statement .= sprintf(" LIMIT %u;", $this->rowCount);
        }
        
        if ($this->debug)
        {
            self::log($statement);
        }
        
        $sth = $this->dbh->prepare($statement);
        if (!$sth)
        {
            return false;
        }
        
        if (!$sth->execute($params))
        {
            return false;
        }
        
        return $sth->rowCount();
    }
        
    public function where()
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
        }
        
        $this->where[] = array(
            'column'   => $column,
            'operator' => $operator,
            'value'    => $value,
        );
        
        return $this;
    }
}
