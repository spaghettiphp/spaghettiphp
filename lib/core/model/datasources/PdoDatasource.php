<?php

class PdoDatasource extends Datasource {
    public $connection;
    protected $config;
    
    public function __construct($config) {
        $this->config = $config;
    }
    // @todo should throw exception
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
        endif;
        
        return $this->connection;
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
    public function fetch($sql) {
        return $this->connection->query($sql);
    }
}

// @todo move to some utils file
function is_hash($var) {
    if(is_array($var)):
        return array_keys($var) !== range(0, sizeof($var) - 1);
    endif;
    return false;
}