<?php

class QueryBuilder extends Object {
    protected $connection;
    protected $values;
    protected static $operators = array(
        '=', '<>', '!=', '<=', '<', '>=', '>', '<=>', 'LIKE', 'REGEXP',
        '\(\)', 'BETWEEN'
    );
    protected static $logical = array(
        'and', 'and not', 'or', 'or not', 'xor', 'not'
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
        $values = array();
        
        $result = $this->evaluate($params['conditions']);
        $params['conditions'] = $result['sql'];
        $values += $result['values'];
        
        return $values;
    }
    public function values() {
        return $this->values;
    }
    protected function evaluate($params, $logical = 'and') {
        $return = array(
            'values' => array(),
            'sql' => array()
        );
        
        foreach($params as $k => $param):
            if(!is_numeric($k)):
                list($field, $fn, $operator) = $this->field($k);
                if(in_array($k, self::$logical)):
                    $result = $this->evaluate($param, $k);
                    $return['sql'] []= '(' . $result['sql'] . ')';
                    $return['values'] += $result['values'];
                elseif(!is_array($param)):
                    $return['sql'] []= $field . ' '. $operator . ' ?';
                    $return['values'] []= $param;
                else:
                    if($fn == 'BETWEEN'):
                        $return['sql'] []= $field . ' BETWEEN ? AND ?';
                    else:
                        $repeat = rtrim(str_repeat('?,', count($param)), ',');
                        $return['sql'] []= $field . ' ' . $fn . '(' . $repeat . ')';
                    endif;
                    $return['values'] += array_values($param);
                endif;
            else:
                $return['sql'] []= $param;
            endif;
        endforeach;
        
        $logical = ' ' . strtolower($logical) . ' ';
        $return['sql'] = implode($logical, $return['sql']);
       
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