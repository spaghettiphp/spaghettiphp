<?php

class PdoDatasource extends Datasource {
    protected $affectedRows;
    protected $schema = array();
    protected $sources = array();
    protected $connection;
    protected $connected;
    protected $config;
    protected $lastQuery;
    protected $params = array(
        'fields' => '*',
        'joins' => array(),
        'conditions' => array(),
        'groupBy' => null,
        'having' => null,
        'order' => null,
        'offset' => null,
        'limit' => null
    );
    
    public function __construct($config) {
        $this->config = $config;
    }
    public function dsn() {
        return $this->config['dsn'];
    }
    public function connect($dsn = null, $username = null, $password = null) {
        if(!$this->connection):
            if(is_null($dsn)):
                $dsn = $this->dsn();
                $username = $this->config['user'];
                $password = $this->config['password'];
            endif;
            $this->connection = new PDO($dsn, $username, $password);
            $this->connected = true;
        endif;
        
        return $this->connection;
    }
    public function disconnect() {
        $this->connected = false;
        $this->connection = null;

        return true;
    }
    public function connection() {
        if(!$this->connected):
            $this->connect();
        endif;

        return $this->connection;
    }
    public function begin() {
        return $this->connection()->beginTransaction();
    }
    public function commit() {
        return $this->connection()->commit();
    }
    public function rollback() {
        return $this->connection()->rollback();
    }
    public function insertId() {
        return $this->connection()->lastInsertId();
    }
    public function affectedRows() {
        return $this->affectedRows;
    }
    public function alias($fields) {
        if(is_array($fields)):
            if(is_hash($fields)):
                foreach($fields as $alias => $field):
                    if(!is_numeric($alias)):
                        $fields[$alias] = $field . ' AS ' . $alias;
                    endif;
                endforeach;
            endif;
            
            $fields = implode(',', $fields);
        endif;
        
        return $fields;
    }
    public function join($params) {
        $params += array(
            'type' => null,
            'on' => null
        );
        
        $join = 'JOIN ' . $this->alias($params['table']);
        
        if($params['type']):
            $join = strtoupper($params['type']) . ' ' . $join;
        endif;
        
        if($params['on']):
            $join .= ' ON ' . $params['on'];
        endif;
        
        return $join;
    }
    public function order($order) {
        if(is_array($order)):
            $order = implode(',', $order);
        endif;
        
        return $order;
    }
    public function values($conditions) {
        return array_slice($conditions, 1);
    }
    public function query($sql, $values = array()) {
        $this->lastQuery = $sql;

        $query = $this->connection()->prepare($sql);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute($values);

        $this->affectedRows = $query->rowCount();

        return $query;
    }
    public function fetchAll($sql) {
        return $this->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    public function read($params) {
        $params += $this->params;
        $values = $this->values($params['conditions']);
        $sql = $this->renderSelect($params);
        $query = $this->query($sql, $values);

        $results = array();
        while($row = $query->fetch()):
            $results []= $row;
        endwhile;

        return $results;
    }
    public function delete($params) {
        $params += $this->params;
        $values = $this->values($params['conditions']);
        $sql = $this->renderDelete($params);
        $query = $this->query($sql, $values);
        
        return $query;
    }


    public function create($table = null, $data = array()) {
        $insertFields = $insertValues = array();
        $schema = $this->describe($table);
        foreach($data as $field => $value):
            $column = isset($schema[$field]) ? $schema[$field]['type'] : null;
            $insertFields []= $field;
            $insertValues []= $this->value($value, $column);
        endforeach;
        $query = $this->renderInsert(array(
            'table' => $table,
            'fields' => join(',', $insertFields),
            'values' => join(',', $insertValues)
        ));
        
        return $this->query($query);
    }
    public function update($table, $params) {
        $updateValues = array();
        $schema = $this->describe($table);
        foreach($params['data'] as $field => $value):
            $column = isset($schema[$field]) ? $schema[$field]['type'] : null;
            $updateValues []= $field . '=' . $this->value($value, $column);
        endforeach;
        $query = $this->renderUpdate(array(
            'table' => $table,
            'conditions' => ($c = $this->sqlConditions($table, $params['conditions'])) ? 'WHERE ' . $c : '',
            'order' => is_null($params['order']) ? '' : 'ORDER BY ' . $params['order'],
            'limit' => is_null($params['limit']) ? '' : 'LIMIT ' . $params['limit'],
            'values' => join(',', $updateValues)
        ));
        
        return $this->query($query);
    }
}