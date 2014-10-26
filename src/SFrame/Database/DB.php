<?php namespace SFrame\Database;

/*
 * A simple database class based on PDO
 * Support master/slave mode
 * 
 * Config example:
 * 
 * One database:
 * array(
 *  driver => ''        // optional, default mysql
 *  host => ''          // optional, default 127.0.0.1
 *  port => ''          // optional, default PDO defaul
 *  charset => 'utf8'   // optional, default utf8
 *  persistent => false // optional, default false
 *  dbname => ''        // required, the name of the database
 *  username => ''      // required, username of the database
 *  password => ''      // required, password of the database
 * )
 * 
 * 1 master 1 slave:
 * array(
 *  master => array(
 *      [one config]
 *  )
 *  slave => array(
 *      [one config]
 *  )
 * )
 * 
 * 1 master multi slaves
 * array(
 *  master => array(
 *      [one config]
 *  )
 *  slaves => array(
 *      array(
 *          [one config]
 *      )
 *      array(
 *          [one config]
 *      )
 *      ...
 *  )
 * )
 */
class DB
{
    const DEFAULT_DRIVER = 'mysql';
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = '';
    const DEFAULT_CHARSET = 'utf8';
    const DEFAULT_PERSISTENT = false;
    
    protected $_master = null;
    protected $_slave = null;
    
    protected $_config_master = array();
    protected $_config_slaves = array();
    
    protected $_error_code = '';
    protected $_error_message = '';

    /**
     * create new based on the given configuration
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        // config master
        $this->_config_master = empty($config['master']) ? $config : $config['master'];
        
        // config slave
        $slaves = empty($config['slaves']) ? array() : $config['slaves'];
        if (!empty($config['slave'])) {
            $slaves[] = $config['slave'];
        }
        $this->_config_slaves = $slaves;
    }

    /**
     * 获取数据库链接
     */
    protected function _connection(array $config)
    {
        if (empty($config['dbname']) || empty($config['username']) || $config['password']) {
            throw new Exception\InvalidArgument('Invalid config');
        }
        
        $charset = empty($config['charset']) ? self::DEFAULT_CHARSET : $config['charset'];
        $persistent = isset($config['persistent']) ? (bool)$config['persistent'] : self::DEFAULT_PERSISTENT;
        $options = array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . $charset . "'",
            \PDO::ATTR_PERSISTENT => $persistent
        );
        
        $driver = empty($config['driver']) ? self::DEFAULT_DRIVER : $config['driver'];
        $host = empty($config['host']) ? self::DEFAULT_HOST : $config['host'];
        $port = empty($config['port']) ? '' : (';port='. $config['port']);
        $dsn = $driver .':host='. $host . $port .';dbname='. $config['dbname'];
        
        try {
            $conn = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (\PDOException $e) {
            throw new Exception\ConnectError($e->getMessage());
        }
        return $conn;
    }

    /**
     * master connection
     * 
     * @return \PDO
     */
    public function _master()
    {
        if (null === $this->_master) {
            $this->_master = $this->_connection($this->_config_master);
        }
        return $this->_master;
    }
    
    
    /**
     * slave connection
     * 
     * @return \PDO
     */
    public function _slave()
    {
        if (empty($this->_config_slave)) {
            return $this->_master();
        }
        if (null === $this->_slave) {
            $this->_slave = $this->_connection($this->_config_slave);
        }
        return $this->_slave;
    }
    
    
    /**
     * Get the error code
     */
    public function getErrorCode()
    {
        return $this->_error_code;
    }
    
    /**
     * Get the error info
     */
    public function getErrorMessage()
    {
        return $this->_error_message;
    }

    /**
     * Get last insert id
     * 
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->_master()->lastInsertId();
    }
    
    
    /**
     * Get columns of the table
     */
    public function getColumns($table)
    {
        $sql = 'SELECT * FROM ' . $this->quoteIdentifier($table);
        $stmt = $this->_slave()->query($sql);
        if ($stmt === false) {
            throw new Exception\InvalidArgument('Invalid table:'. $table);
        }
        $column_count = $stmt->columnCount();
        $columns = array();
        for ($i = 0; $i < $column_count; $i++) {
            $info = $stmt->getColumnMeta($i);
            $columns[$info['name']] = array(
                'name' => $info['name'],
                'native_type' => $info['native_type'],
                'len' => $info['len'],
                'precision' => $info['precision']
            );
        }
        return $columns;
    }
    
    
    /**
     * Execute the sql
     * 
     * @param string $sql
     * @param array $bind
     * @return mixed result
     */
    public function query($sql, $bind = array())
    {
        if (!is_array($bind)) {
            $bind = array($bind);
        }
        $stmt = $this->_master()->prepare($sql);
        $result = $stmt->execute($bind);
        if (!$result) {
            $error = $stmt->errorInfo();
            if (!empty($error[2])) {
                $this->_error_code = $error[1];
                $this->_error_message = $error[2];
            }
        }
        return $result;
    }
    
    
    /**
     * Prepare the sql
     *
     * @param string $sql
     * @param array $bind
     * @return PDOStatement
     */
    public function prepare($sql, $bind = array())
    {
        if (!is_array($bind)) {
            $bind = array($bind);
        }
        $stmt = $this->_slave()->prepare($sql);
        $stmt->execute($bind);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $error = $stmt->errorInfo();
        if (!empty($error[2])) {
            $this->_error_code = $error[1];
            $this->_error_message = $error[2];
        }
        return $stmt;
    }
    
    
    /**
     * insert
     *
     * @param string $table
     * @param array $bind
     * @return bool result
     */
    public function insert($table, array $bind)
    {
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col);
            $vals[] = '?';
        }
        $sql = "INSERT INTO " . $this->quoteIdentifier($table) . ' (' . implode(', ', $cols) . ') VALUES (' . implode(', ', $vals) . ')';
        return $this->query($sql, array_values($bind));
    }
    
    
    /**
     * 更新数据库
     */
    public function update($table, array $bind, $where = '')
    {
        $set = array();
        foreach ($bind as $col => $val) {
            $val = '?';
            $set[] = $this->quoteIdentifier($col) . ' = ' . $val;
        }
        $where = $this->where($where);
        $sql = 'UPDATE ' . $this->quoteIdentifier($table) . ' SET ' . implode(', ', $set) . (($where) ? " WHERE $where" : '');
        return $this->query($sql, array_values($bind));
    }
    
    
    /**
     * save
     * update if record exits, if not insert
     */
    public function save($table, array $bind, $where = '')
    {
        $where = $this->where($where);
        $sql = 'SELECT COUNT(*) FROM ' . $this->quoteIdentifier($table) . (($where) ? " WHERE $where" : '');
        if ($this->fetchOne($sql)) {
            return $this->update($table, $bind, $where);
        } else {
            return $this->insert($table, $bind);
        }
    }
    
    
    /**
     * Delete
     */
    public function delete($table, $where = '')
    {
        $where = $this->where($where);
        $sql = 'DELETE FROM ' . $this->quoteIdentifier($table) . (($where) ? " WHERE $where" : '');
        return $this->query($sql);
    }
    
    
    /**
     * Fetch all
     */
    public function fetchAll($sql, $bind = array(), $fetch_mode = null)
    {
        $stmt = $this->prepare($sql, $bind);
        return $stmt->fetchAll($fetch_mode);
    }
    
    
    /**
     * Fetch all, and set index key
     */
    public function fetchAllIndexBy($index, $sql, $bind = array(), $fetch_mode = null)
    {
        $data = $this->fetchAll($sql, $bind, $fetch_mode);
        $result = array();
        foreach ($data as $row) {
            $result[$row[$index]] = $row;
        }
        return $result;
    }
    
    
    /**
     * Fetch one row
     */
    public function fetchRow($sql, $bind = array(), $fetch_mode = null)
    {
        $stmt = $this->prepare($sql, $bind);
        return $stmt->fetch($fetch_mode);
    }
    
    
    /**
     * Fetch a Col
     */
    public function fetchCol($sql, $bind = array())
    {
        $stmt = $this->prepare($sql, $bind);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }
    
    
    /**
     * Fetch pairs 0:key, 1:value
     */
    public function fetchPairs($sql, $bind = array())
    {
        $stmt = $this->prepare($sql, $bind);
        $data = array();
        while (true == ($row = $stmt->fetch(PDO::FETCH_NUM))) {
            $data[$row[0]] = $row[1];
        }
        return $data;
    }
    
    
    /**
     * Fetch one
     */
    public function fetchOne($sql, $bind = array())
    {
        $stmt = $this->prepare($sql, $bind);
        $result = $stmt->fetchColumn(0);
        return $result;
    }
    
    
    /**
     * 获取组装后的where条件
     *
     * array('x=?'=>'x')
     * array('x>?'=>2, 'y=?'=>'y')
     * array('x in?'=>array(1,2,3))
     * array('x between?'=>array(1,2))
     * array('x like?'=>'%x')
     * array('x=?'=>x, 'or'=>array('x=?'=>'x', 'y>?'=>2))
     *
     * @param string|array $where
     * @return string
     */
    public function where($where)
    {
        if (!is_array($where)) {
            return $where;
        }
        $where_str = '';
        $i = 0;
        foreach ($where as $cond => $term) {
            if (strtolower($cond) == 'or') {
                $where_str .= ($i ? ' OR ' : '') . '(' . $this->where($term) . ')';
            } elseif (strtolower($cond) == 'and') {
                $where_str .= ($i ? ' AND ' : '') . '(' . $this->where($term) . ')';
            } else {
                
                if (!is_int($cond)) {
                    if (!strpos($cond, '?')) {
                        throw new Exception\InvalidArgument;
                    }
                    $self = $this;
                    if (stripos($cond, ' in')) {
                        $term = preg_replace_callback('/\s+in\s*\?/i', function() use($term, $self){
                            return ' IN('. is_array($term) ? $self->quote($term) : $term .')';
                        }, $cond);
                    } elseif (stripos($cond, ' between')) {
                        $term = preg_replace_callback('/\s+between\s*\?/i', function() use($term, $self){
                            return ' BETWEEN '. is_array($term) ? ($self->quote($term[0]) .' AND '. $self->quote($term[1])) : $term;
                        }, $cond);
                    } elseif (stripos($cond, ' like')) {
                        $term = preg_replace_callback('/\s+like\s*\?/i', function() use($term, $self){
                            return ' LIKE '. $self->quote($term);
                        }, $cond);
                    } else {
                        $term = str_replace('?', $this->quote($term), $cond);
                    }
                }
                
                if (preg_match('/^\s*or\s+/i', $term)) {
                    $where_str .= preg_replace('/^\s*or\s+/i', ' OR ', $term);
                } else {
                    $where_str .= ($i ? ' AND ' : '') . $term;
                }
            }
            $i++;
        }
        return $where_str;
    }
    
    
    /**
     * Safely quotes a value for an SQL statement.
     */
    public function quote($value)
    {
        if (is_array($value)) {
            foreach ($value as &$val) {
                $val = $this->quote($val);
            }
            return implode(', ', $value);
        } elseif (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }
        return "'" . addcslashes($value, "\000\n\r\\'\"\032") . "'";
    }
    
    
    /**
     * Quotes an identifier.
     *
     * <code>
     * $adapter->quoteIdentifier('myschema.mytable')
     * </code>
     * Returns: "myschema"."mytable"
     *
     * <code>
     * $adapter->quoteIdentifier(array('myschema','my.table'))
     * </code>
     * Returns: "myschema"."my.table"
     *
     * @param type $ident
     * @return string The quoted identifier.
     */
    public function quoteIdentifier($ident)
    {
        return $this->_quoteIdentifierAs($ident, null);
    }
    
    
    /**
     * Quote a column identifier and alias.
     *
     * @param string|array|Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An alias for the column.
     * @return string The quoted identifier and alias.
     */
    public function quoteColumnAs($ident, $alias)
    {
        return $this->_quoteIdentifierAs($ident, $alias);
    }
    
    
    /**
     * Quote a table identifier and alias.
     *
     * @param string|array|Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An alias for the table.
     * @return string The quoted identifier and alias.
     */
    public function quoteTableAs($ident, $alias = null)
    {
        return $this->_quoteIdentifierAs($ident, $alias);
    }
    
    
    /**
     * Quote an identifier and an optional alias.
     *
     * @param string|array $ident The identifier or expression.
     * @param string $alias An optional alias.
     * @param string $as The string to add between the identifier/expression and the alias.
     * @return string The quoted identifier and alias.
     */
    protected function _quoteIdentifierAs($ident, $alias = null, $as = ' AS ')
    {
        if (is_string($ident)) {
            $ident = explode('.', $ident);
        }
        if (is_array($ident)) {
            $segments = array();
            foreach ($ident as $segment) {
                $segments[] = $this->_quoteIdentifier($segment);
            }
            if ($alias !== null && end($ident) == $alias) {
                $alias = null;
            }
            $quoted = implode('.', $segments);
        } else {
            $quoted = $this->_quoteIdentifier($ident);
        }
        if ($alias !== null) {
            $quoted .= $as . $this->_quoteIdentifier($alias);
        }
        return $quoted;
    }
    
    
    /**
     * Quote an identifier.
     *
     * @param  string $value The identifier or expression.
     * @return string        The quoted identifier and alias.
     */
    protected function _quoteIdentifier($value)
    {
        $q = '`';
        return ($q . str_replace("$q", "$q$q", $value) . $q);
    }
}
