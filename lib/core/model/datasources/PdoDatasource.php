<?php

require 'lib/core/model/QueryBuilder.php';

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
        $this->connect();
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
    public function begin() {
        return $this->connection->beginTransaction();
    }
    public function commit() {
        return $this->connection->commit();
    }
    public function rollback() {
        return $this->connection->rollBack();
    }
    public function insertId() {
        return $this->connection->lastInsertId();
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
        if(is_array($params)):
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
        else:
            $join = $params;
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

        $query = $this->connection->prepare($sql);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute($values);

        $this->affectedRows = $query->rowCount();

        return $query;
    }
    public function fetchAll($sql) {
        return $this->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
     public function escape($value) {
        if(is_null($value)):
            return 'NULL';
        else:
            return $this->connection->quote($value);
        endif;
    }
    public function create($params) {
        $params += $this->params;
        $values = array_values($params['values']);
        $sql = $this->renderInsert($params);
        $query = $this->query($sql, $values);
        
        return $query;
    }
    public function read($params) {
        $params += $this->params;
        
        $query = new QueryBuilder($this);
        $sql = $query->buildQuery('select', $params);
        $values = $query->values();

        $query = $this->query($sql, $values);

        $results = array();
        while($row = $query->fetch()):
            $results []= $row;
        endwhile;

        return $results;
    }
    public function update($params) {
        $params += $this->params;
        $values = array_merge(
            array_values($params['values']),
            $this->values($params['conditions'])
        );
        
        $sql = $this->renderUpdate($params);
        $query = $this->query($sql, $values);
        
        return $query;
    }
    public function delete($params) {
        $params += $this->params;
        $values = $this->values($params['conditions']);
        $sql = $this->renderDelete($params);
        $query = $this->query($sql, $values);
        
        return $query;
    }
}