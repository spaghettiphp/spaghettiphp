<?php

class MysqlDatasource extends Datasource {
    protected $schema = array();
    protected $sources = array();
    protected $connection;
    protected $results;
    protected $transactionStarted = false;
    protected $comparison = array('=', '<>', '!=', '<=', '<', '>=', '>', '<=>', 'LIKE', 'REGEXP');
    protected $logic = array('or', 'or not', '||', 'xor', 'and', 'and not', '&&', 'not');
    public $connected = false;

    public function connect() {
        $this->connection = @mysql_connect($this->config['host'], $this->config['user'], $this->config['password']);
        if(@mysql_select_db($this->config['database'], $this->connection)):
            $this->connected = true;
        else:
            $this->error('connectionError');
        endif;
        return $this->connection;
    }
    public function disconnect() {
        if(mysql_close($this->connection)):
            $this->connected = false;
            $this->connection = null;
        endif;
        return !$this->connected;
    }
    public function &getConnection() {
        if(!$this->connected):
            $this->connect();
        endif;
        return $this->connection;
    }
    public function query($sql = null) {
        $this->results = mysql_query($sql, $this->getConnection());
        return $this->results;
    }
    public function fetch($sql = null) {
        if(!is_null($sql) && !$this->query($sql)):
            return false;
        elseif($this->hasResult()):
            return $this->fetchRow();
        else:
            return null;
        endif;
    }
    public function fetchAll($sql = null) {
        if(!is_null($sql) && !$this->query($sql)):
            return false;
        elseif($this->hasResult()):
            $results = array();
            while($result = $this->fetch()):
                $results []= $result;
            endwhile;
            return $results;
        else:
            return null;
        endif;
    }
    public function fetchRow($results = null) {
        $results = is_null($results) ? $this->results : $results;
        return mysql_fetch_assoc($results);
    }
    public function hasResult() {
        return is_resource($this->results);
    }
    public function column($column) {
        preg_match('/([a-z]*)\(?([^\)]*)?\)?/', $column, $type);
        list($column, $type, $limit) = $type;
        if(in_array($type, array('date', 'time', 'datetime', 'timestamp'))):
            return $type;
        elseif(($type == 'tinyint' && $limit == 1) || $type == 'boolean'):
            return 'boolean';
        elseif(strstr($type, 'int')):
            return 'integer';
        elseif(strstr($type, 'char') || $type == 'tinytext'):
            return 'string';
        elseif(strstr($type, 'text')):
            return 'text';
        elseif(strstr($type, 'blob') || $type == 'binary'):
            return 'binary';
        elseif(in_array($type, array('float', 'double', 'real', 'decimal'))):
            return 'float';
        elseif($type == 'enum' || $type = 'set'):
            return $type . '(' . $limit . ')';
        endif;
    }
    public function listSources() {
        if(empty($this->sources)):
            $sources = $this->query('SHOW TABLES FROM ' . $this->config['database']);
            while($source = mysql_fetch_array($sources)):
                $this->sources []= $source[0];
            endwhile;
        endif;
        return $this->sources;
    }
    public function describe($table) {
        if(!isset($this->schema[$table])):
            if(!$this->query('SHOW COLUMNS FROM ' . $table)) return false;
            $columns = $this->fetchAll();
            $schema = array();
            foreach($columns as $column):
                $schema[$column['Field']] = array(
                    'type' => $this->column($column['Type']),
                    'null' => $column['Null'] == 'YES' ? true : false,
                    'default' => $column['Default'],
                    'key' => $column['Key'],
                    'extra' => $column['Extra']
                );
            endforeach;
            $this->schema[$table] = $schema;
        endif;
        return $this->schema[$table];
    }
    public function begin() {
        return $this->transactionStarted = $this->query('START TRANSACTION');
    }
    public function commit() {
        $this->transactionStarted = !$this->query('COMMIT');
        return !$this->transactionStarted;
    }
    public function rollback() {
        $this->transactionStarted = !$this->query('ROLLBACK');
        return !$this->transactionStarted;
    }
    public function create($table = null, $data = array()) {
        $insertFields = $insertValues = array();
        $schema = $this->describe($table);
        foreach($data as $field => $value):
            $column = isset($schema[$field]) ? $schema[$field]['type'] : null;
            $insertFields []= $field;
            $insertValues []= $this->value($value, $column);
        endforeach;
        $query = $this->renderSql('insert', array(
            'table' => $table,
            'fields' => join(',', $insertFields),
            'values' => join(',', $insertValues)
        ));
        return $this->query($query);
    }
    public function read($table = null, $params = array()) {
        $query = $this->renderSql('select', array(
            'table' => $table,
            'fields' => is_array($f = $params['fields']) ? join(',', $f) : $f,
            'conditions' => ($c = $this->sqlConditions($table, $params['conditions'])) ? 'WHERE ' . $c : '',
            'order' => is_null($params['order']) ? '' : 'ORDER BY ' . $params['order'],
            'groupBy' => !isset($params['groupBy']) ? '' : 'GROUP BY ' . $params['groupBy'],
            'limit' => is_null($params['limit']) ? '' : 'LIMIT ' . $params['limit']
        ));
        return $this->fetchAll($query);
    }
    public function update($table = null, $params = array()) {
        $updateValues = array();
        $schema = $this->describe($table);
        foreach($params['data'] as $field => $value):
            $column = isset($schema[$field]) ? $schema[$field]['type'] : null;
            $updateValues []= $field . '=' . $this->value($value, $column);
        endforeach;
        $query = $this->renderSql('update', array(
            'table' => $table,
            'conditions' => ($c = $this->sqlConditions($table, $params['conditions'])) ? 'WHERE ' . $c : '',
            'order' => is_null($params['order']) ? '' : 'ORDER BY ' . $params['order'],
            'limit' => is_null($params['limit']) ? '' : 'LIMIT ' . $params['limit'],
            'values' => join(',', $updateValues)
        ));
        return $this->query($query);
    }
    public function delete($table = null, $params = array()) {
        $query = $this->renderSql('delete', array(
            'table' => $table,
            'conditions' => ($c = $this->sqlConditions($table, $params['conditions'])) ? 'WHERE ' . $c : '',
            'order' => is_null($params['order']) ? '' : 'ORDER BY ' . $params['order'],
            'limit' => is_null($params['limit']) ? '' : 'LIMIT ' . $params['limit']
        ));
        return $this->query($query);
    }
    public function count($table = null, $params) {
        $query = $this->renderSql('select', array(
            'table' => $table,
            'conditions' => ($c = $this->sqlConditions($table, $params['conditions'])) ? 'WHERE ' . $c : '',
            'fields' => 'COUNT(' . (is_array($f = $params['fields']) ? join(',', $f) : $f) . ') AS count'
        ));
        $results = $this->fetchAll($query);
        return $results[0]['count'];
    }
    public function renderSql($type, $data = array()) {
        switch($type):
            case 'select':
                return "SELECT {$data['fields']} FROM {$data['table']} {$data['conditions']} {$data['groupBy']} {$data['order']} {$data['limit']}";
            case 'delete':
                return "DELETE FROM {$data['table']} {$data['conditions']} {$data['order']} {$data['limit']}";
            case 'insert':
                return "INSERT INTO {$data['table']}({$data['fields']}) VALUES({$data['values']})";
            case 'update':
                return "UPDATE {$data['table']} SET {$data['values']} {$data['conditions']} {$data['order']} {$data['limit']}";
        endswitch;
    }
    public function value($value, $column = null) {
        switch($column):
            case 'boolean':
                if($value === true):
                    return '1';
                elseif($value === false):
                    return '0';
                else:
                    return !empty($value) ? '1' : '0';
                endif;
            case 'integer':
            case 'float':
                if($value === '' or is_null($value)):
                    return 'NULL';
                elseif(is_numeric($value)):
                    return $value;
                endif;
            default:
                if(is_null($value)):
                    return 'NULL';
                endif;
                return '\'' . mysql_real_escape_string($value, $this->connection) . '\'';
        endswitch;
    }
    public function sqlConditions($table, $conditions, $logical = 'AND') {
        if(is_array($conditions)):
            $sql = array();
            foreach($conditions as $key => $value):
                if(is_numeric($key)):
                    if(is_string($value)):
                        $sql []= $value;
                    else:
                        $sql []= '(' . $this->sqlConditions($table, $value) . ')';
                    endif;
                else:
                    if(in_array($key, $this->logic)):
                        $sql []= '(' . $this->sqlConditions($table, $value, strtoupper($key)) . ')';
                    elseif(is_array($value)):
                        foreach($value as $k => $v):
                            $value[$k] = $this->value($v, null);
                        endforeach;
                        if(preg_match('/([\w_]+) (BETWEEN)/', $key, $regex)):
                            $condition = $regex[1] . ' BETWEEN ' . join(' AND ', $value);
                        else:
                            $condition = $key . ' IN (' . join(',', $value) . ')';
                        endif;
                        $sql []= $condition;
                    else:
                        $comparison = '=';
                        if(preg_match('/([\w_]+) (' . join('|', $this->comparison) . ')/', $key, $regex)):
                            list($regex, $key, $comparison) = $regex;
                        endif;
                        $value = $this->value($value, $this->fieldType($table, $key));
                        $sql []= $key . ' ' . $comparison . ' ' . $value;
                    endif;
                endif;
            endforeach;
            $sql = join(' ' . $logical . ' ', $sql);
        else:
            $sql = $conditions;
        endif;
        return $sql;
    }
    public function fieldType($table, $field) {
        if(isset($this->schema[$table]) && isset($this->schema[$table][$field])):
            return $this->schema[$table][$field]['type'];
        endif;
        return null;
    }
    public function getInsertId() {
        return mysql_insert_id($this->getConnection());
    }
    public function getAffectedRows() {
        return mysql_affected_rows($this->getConnection());
    }
}