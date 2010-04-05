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
    public function read($params) {
        $params += $this->params;
        $values = array_slice($params['conditions'], 1);
        
        $sql = $this->renderSelect($params);
        $query = $this->query($sql, $values);

        $results = array();
        while($row = $query->fetch()):
            $results []= $row;
        endwhile;

        return $results;
    }
}