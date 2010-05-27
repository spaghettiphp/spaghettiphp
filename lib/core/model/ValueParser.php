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
        list($this->values, $this->conditions) = $this->evaluate($conditions);
    }
    public function conditions() {
        return $this->conditions;
    }
    public function values() {
        return $this->values;
    }
    protected function evaluate($params, $logical = 'and') {
        $values = $sql = array();
        foreach($params as $k => $param):
            if(!is_numeric($k)):
                if(in_array($k, self::$logical)):
                    $result = $this->evaluate($param, $k);
                    $sql []= '(' . $result[1] . ')';
                    $values = array_merge($values, $result[0]);
                else:
                    $field = $this->field($k);
                    if(is_null($field)):
                        $sql []= $k;
                        $values += array_values($param);
                        continue;
                    endif;

                    list($field, $operator) = $field;
                    if(!is_array($param)):
                        $sql []= $field . ' '. $operator . ' ?';
                        $values []= $param;
                    else:
                        $repeat = rtrim(str_repeat('?,', count($param)), ',');
                        $sql []= $field . ' IN(' . $repeat . ')';
                        $values += array_values($param);
                    endif;
                endif;
            else:
                $sql []= $param;
            endif;
        endforeach;
        
        $logical = ' ' . strtoupper($logical) . ' ';
        $sql = implode($logical, $sql);
       
        return array($values, $sql);
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