<?php

require_once 'lib/core/model/datasources/PdoDatasource.php';

class MySqlDatasource extends PdoDatasource {
    protected $comparison = array('=', '<>', '!=', '<=', '<', '>=', '>', '<=>', 'LIKE', 'REGEXP');
    protected $logic = array('or', 'or not', '||', 'xor', 'and', 'and not', '&&', 'not');
    protected $params = array(
        'fields' => '*',
        'joins' => array(),
        'conditions' => null,
        'groupBy' => null,
        'having' => null,
        'order' => null,
        'offset' => null,
        'limit' => null
    );

    // @todo add missing DSN elements
    public function dsn() {
        return 'mysql:host=' . $this->config['host'] . ';dbname=' . $this->config['database'];
    }
    public function listSources() {
        if(empty($this->sources)):
            $sources = $this->query('SHOW TABLES FROM ' . $this->config['database']);
            foreach($sources as $source):
                $this->sources []= $source[0];
            endforeach;
        endif;
        
        return $this->sources;
    }
    public function describe($table) {
        if(!isset($this->schema[$table])):
            $columns = $this->fetchAll('SHOW COLUMNS FROM ' . $table);
            $schema = array();
            foreach($columns as $column):
                $schema[$column['Field']] = array(
                    'type' => $this->column($column['Type']),
                    'null' => $column['Null'] == 'YES' ? true : false,
                    'default' => $column['Default'],
                    'key' => $column['Key'],
                    'extra' => $column['Extra']
                );
            endforeach;
            $this->schema[$table] = $schema;
        endif;
        
        return $this->schema[$table];
    }
    public function column($column) {
        preg_match('/([a-z]*)\(?([^\)]*)?\)?/', $column, $type);
        list($column, $type, $limit) = $type;
        if(in_array($type, array('date', 'time', 'datetime', 'timestamp'))):
            return $type;
        elseif(($type == 'tinyint' && $limit == 1) || $type == 'boolean'):
            return 'boolean';
        elseif(strstr($type, 'int')):
            return 'integer';
        elseif(strstr($type, 'char') || $type == 'tinytext'):
            return 'string';
        elseif(strstr($type, 'text')):
            return 'text';
        elseif(strstr($type, 'blob') || $type == 'binary'):
            return 'binary';
        elseif(in_array($type, array('float', 'double', 'real', 'decimal'))):
            return 'float';
        elseif($type == 'enum' || $type = 'set'):
            return $type . '(' . $limit . ')';
        endif;
    }
    public function create($table = null, $data = array()) {
        $insertFields = $insertValues = array();
        $schema = $this->describe($table);
        foreach($data as $field => $value):
            $column = isset($schema[$field]) ? $schema[$field]['type'] : null;
            $insertFields []= $field;
            $insertValues []= $this->value($value, $column);
        endforeach;
        $query = $this->renderSql('insert', array(
            'table' => $table,
            'fields' => join(',', $insertFields),
            'values' => join(',', $insertValues)
        ));
        
        return $this->query($query);
    }
    public function read($table, $params) {
        $params['table'] = $table;
        $query = $this->renderSelect($params);
        
        return $this->fetchAll($query);
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
    public function delete($table, $params = array()) {
        $query = $this->renderSql('delete', array(
            'table' => $table,
            'conditions' => ($c = $this->sqlConditions($table, $params['conditions'])) ? 'WHERE ' . $c : '',
            'order' => is_null($params['order']) ? '' : 'ORDER BY ' . $params['order'],
            'limit' => is_null($params['limit']) ? '' : 'LIMIT ' . $params['limit']
        ));
        
        return $this->query($query);
    }
    public function count($table, $params) {
        $params['table'] = $table;
        $params['fields'] = array(
            'count' => 'COUNT(' . $this->alias($params['fields']) . ')'
        );
        $query = $this->renderSelect($params);
        $results = $this->fetchAll($query);
        
        return $results[0]['count'];
    }
    public function renderSql($type, $data = array()) {
        switch($type):
            case 'delete':
                return "DELETE FROM {$data['table']} {$data['conditions']} {$data['order']} {$data['limit']}";
            case 'insert':
                return "INSERT INTO {$data['table']}({$data['fields']}) VALUES({$data['values']})";
        endswitch;
    }

    public function renderSelect($params) {
        $params += $this->params;
        
        $sql = 'SELECT ' . $this->alias($params['fields']);
        $sql .= ' FROM ' . $this->alias($params['table']);
        
        if(!empty($params['joins'])):
            foreach($params['joins'] as $join):
                $sql .= ' ' . $this->join($join);
            endforeach;
        endif;
        
        if($params['conditions']):
            $sql .= ' WHERE ' . $this->sqlConditions($params['"table'], $params['conditions']);
        endif;
        
        if($params['groupBy']):
            $sql .= ' GROUP BY ' . $this->alias($params['groupBy']);
        endif;
        
        if($params['having']):
            $sql .= ' HAVING ' . $params['having'];
        endif;

        if($params['order']):
            $sql .= ' ORDER BY ' . $this->order($params['order']);
        endif;
        
        if($params['offset'] || $params['limit']):
            $sql .= ' LIMIT ' . $this->limit($params['offset'], $params['limit']);
        endif;
        
        return $sql;
    }
    public function renderUpdate($params) {
        return "UPDATE {$params['table']} SET {$params['values']} {$params['conditions']} {$params['order']} {$params['limit']}";
    }
    public function limit($offset, $limit) {
        if(!is_null($offset)):
            $limit = $offset . ',' . $limit;
        endif;
        
        return $limit;
    }



    public function value($value, $column = null) {
        switch($column):
            case 'boolean':
                if($value === true):
                    return '1';
                elseif($value === false):
                    return '0';
                else:
                    return !empty($value) ? '1' : '0';
                endif;
            case 'integer':
            case 'float':
                if($value === '' or is_null($value)):
                    return 'NULL';
                elseif(is_numeric($value)):
                    return $value;
                endif;
            default:
                if(is_null($value)):
                    return 'NULL';
                endif;
                return '\'' . mysql_real_escape_string($value, $this->connection) . '\'';
        endswitch;
    }
    public function sqlConditions($table, $conditions, $logical = 'AND') {
        if(is_array($conditions)):
            $sql = array();
            foreach($conditions as $key => $value):
                if(is_numeric($key)):
                    if(is_string($value)):
                        $sql []= $value;
                    else:
                        $sql []= '(' . $this->sqlConditions($table, $value) . ')';
                    endif;
                else:
                    if(in_array($key, $this->logic)):
                        $sql []= '(' . $this->sqlConditions($table, $value, strtoupper($key)) . ')';
                    elseif(is_array($value)):
                        foreach($value as $k => $v):
                            $value[$k] = $this->value($v, null);
                        endforeach;
                        if(preg_match('/([\w_]+) (BETWEEN)/', $key, $regex)):
                            $condition = $regex[1] . ' BETWEEN ' . join(' AND ', $value);
                        else:
                            $condition = $key . ' IN (' . join(',', $value) . ')';
                        endif;
                        $sql []= $condition;
                    else:
                        $comparison = '=';
                        if(preg_match('/([\w_]+) (' . join('|', $this->comparison) . ')/', $key, $regex)):
                            list($regex, $key, $comparison) = $regex;
                        endif;
                        $value = $this->value($value, $this->fieldType($table, $key));
                        $sql []= $key . ' ' . $comparison . ' ' . $value;
                    endif;
                endif;
            endforeach;
            $sql = join(' ' . $logical . ' ', $sql);
        else:
            $sql = $conditions;
        endif;
        
        return $sql;
    }
    public function fieldType($table, $field) {
        if(isset($this->schema[$table]) && isset($this->schema[$table][$field])):
            return $this->schema[$table][$field]['type'];
        endif;
        return null;
    }
}