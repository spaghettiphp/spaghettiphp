<?php

class QueryBuilder extends Object {
    protected $connection;
    protected $values;
    protected static $operators = array(
        '=', '<>', '!=', '<=', '<', '>=', '>', '<=>', 'LIKE', 'REGEXP',
        '\(\)', 'BETWEEN'
    );

    public function __construct($connection) {
        $this->connection = $connection;
    }
    public function buildQuery($statement, $params) {
        $this->values = $this->extractValues($params);
        $method = 'render' . ucwords($statement);

        return $this->connection->$method($params);
    }
    public function extractValues(&$params) {
        $iterate = array('conditions');
        $values = array();
        
        foreach($iterate as $key):
            $result = $this->evaluate($params[$key]);
            $params[$key] = $result['sql'];
            $values += $result['values'];
        endforeach;
        
        return $values;
    }
    public function values() {
        return $this->values;
    }
    protected function evaluate($params) {
        $return = array(
            'values' => array(),
            'sql' => array()
        );
        
        foreach($params as $k => $param):
            if(!is_numeric($k)):
                list($field, $fn, $operator) = $this->field($k);
                if(!is_array($param)):
                    $return['sql'] []= $field . ' '. $operator . ' ?';
                    $return['values'] []= $param;
                else:
                    $repeat = rtrim(str_repeat('?,', count($param)), ',');
                    $return['sql'] []= $field . ' ' . $fn . '(' . $repeat . ')';
                    $return['values'] += array_values($param);
                endif;
            endif;
        endforeach;
        
        $return['sql'] = implode(' AND ', $return['sql']);
       
        return $return;
    }
    protected function field($field) {
        $regex = '/^([\w_]+)\s?(\w+)?(' . join('|', self::$operators) . ')?$/';
        preg_match($regex, $field, $result);
        array_shift($result);
        if(empty($result[2])):
            $result[2] = '=';
        endif;
        
        return $result;
    }
}