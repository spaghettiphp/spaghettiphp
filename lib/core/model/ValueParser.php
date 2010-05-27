<?php

class ValueParser extends Object {
    protected $conditions;
    protected $values;
    protected static $operators = array(
        '=', '<>', '!=', '<=', '<', '>=', '>', '<=>', 'LIKE', 'REGEXP'
    );
    protected static $logical = array(
        'and', 'and not', 'or', 'or not', 'xor', 'not'
    );

    public function __construct($conditions) {
        $this->conditions = $conditions;
    }
    public function conditions() {
        if(is_null($this->values)):
            list($this->values, $this->conditions) = $this->extractValues();
        endif;

        return $this->conditions;
    }
    public function values() {
        if(is_null($this->values)):
            list($this->values, $this->conditions) = $this->extractValues();
        endif;

        return $this->values;
    }
    protected function extractValues() {
        $result = $this->evaluate($this->conditions);

        return array($result['values'], $result['sql']);
    }
    protected function evaluate($params, $logical = 'and') {
        $return = array(
            'values' => array(),
            'sql' => array()
        );
        foreach($params as $k => $param):
            if(!is_numeric($k)):
                if(in_array($k, self::$logical)):
                    $result = $this->evaluate($param, $k);
                    $return['sql'] []= '(' . $result['sql'] . ')';
                    $return['values'] = array_merge($return['values'], $result['values']);
                else:
                    $field = $this->field($k);
                    if(is_null($field)):
                        $return['sql'] []= $k;
                        $return['values'] += array_values($param);
                        continue;
                    endif;

                    list($field, $operator) = $field;
                    if(!is_array($param)):
                        $return['sql'] []= $field . ' '. $operator . ' ?';
                        $return['values'] []= $param;
                    else:
                        $repeat = rtrim(str_repeat('?,', count($param)), ',');
                        $return['sql'] []= $field . ' IN(' . $repeat . ')';
                        $return['values'] += array_values($param);
                    endif;
                endif;
            else:
                $return['sql'] []= $param;
            endif;
        endforeach;
        
        $logical = ' ' . strtoupper($logical) . ' ';
        $return['sql'] = implode($logical, $return['sql']);
       
        return $return;
    }
    protected function field($field) {
        $regex = '/^([\S]+)(?:\s?(' . join('|', self::$operators) . '))?$/';
        if(preg_match($regex, $field, $result)):
            array_shift($result);
            if(!isset($result[1])):
                $result[1] = '=';
            endif;

            return $result;
        endif;

        return null;
    }
}