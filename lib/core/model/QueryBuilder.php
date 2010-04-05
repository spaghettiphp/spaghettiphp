<?php

class QueryBuilder extends Object {
    protected $connection;
    protected $lastJoin;
    public $conditions;
    public $fields = '*';
    public $groupBy;
    public $having;
    public $joins = array();
    public $limit;
    public $offset;
    public $operation;
    public $order;
    public $table;

    public function __construct($connection) {
        $this->connection = $connection;
    }
    public function __toString() {
        return '(' . $this->toString() . ')';
    }
    public function toString() {
        $params = array(
            'conditions' => $this->conditions,
            'fields' => $this->fields,
            'groupBy' => $this->groupBy,
            'having' => $this->having,
            'joins' => $this->joins,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'order' => $this->order,
            'table' => $this->table
        );
        $method = 'render' . ucwords($this->operation);
        
        return $this->connection->$method($params);
    }
    public function getIterator() {
        return $this->fetch();
    }
    public function select($table) {
        $this->operation = 'select';
        $this->table = $table;

        return $this;
    }
    public function fields($fields) {
        $this->fields = $fields;

        return $this;
    }
    public function join($join, $type = null, $on = null) {
        $this->lastJoin = count($this->joins);
        $this->joins []= array(
            'table' => $join,
            'type' => $type,
            'on' => $on
        );

        return $this;
    }
    public function on($conditions) {
        $this->joins[$this->lastJoin]['on'] = $conditions;

        return $this;
    }
    public function where($conditions) {
        $this->conditions = $conditions;

        return $this;
    }
    public function groupBy($group) {
        $this->groupBy = $group;

        return $this;
    }
    public function having($having) {
        $this->having = $having;

        return $this;
    }
    public function order($order) {
        $this->order = $order;

        return $this;
    }
    public function offset($offset) {
        $this->offset = $offset;

        return $this;
    }
    public function limit($limit) {
        $this->limit = $limit;

        return $this;
    }
    public function fetch() {
        return $this->connection->fetch($this->toString());
    }    
}