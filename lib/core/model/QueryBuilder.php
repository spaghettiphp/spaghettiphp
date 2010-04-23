<?php

class QueryBuilder extends Object {
    protected $connection;
    protected $values;

    public function __construct($connection) {
        $this->connection = $connection;
    }
    public function buildQuery($statement, $params) {
        $this->values = $this->extractValues($params);
        $method = 'render' . ucwords($statement);

        return $this->connection->$method($params);
    }
    public function extractValues(&$params) {
        $values = array();
        
        foreach($params['conditions'] as $k => $param):
            if(!is_numeric($k)):
                $params['conditions'][$k] = $k . ' ?';
                $values []= $param;
            endif;
        endforeach;
        
        $params['conditions'] = implode(' AND ', array_values($params['conditions']));
        
        return $values;
    }
    public function values() {
        return $this->values;
    }
}