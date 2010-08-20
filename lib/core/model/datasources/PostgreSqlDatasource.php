<?php

require_once 'lib/core/model/datasources/PdoDatasource.php';

class PostgreSqlDatasource extends PdoDatasource {
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
    // @todo add missing DSN elements
    public function dsn() {
        return 'pgsql:host=' . $this->config['host'] . ';dbname=' . $this->config['database'] . ';port=' . $this->config['port'];
    }
    public function listSources() {
        if(empty($this->sources)):
            $sql = "SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_type='BASE TABLE'";
            $query = $this->connection->prepare($sql);
            $query->setFetchMode(PDO::FETCH_NUM);
            $sources = $query->execute();
            while($source = $query->fetch()):
                $this->sources []= $source[0];
            endwhile;
        endif;
        return $this->sources;
    }
    public function describe($table) {
        if(!isset($this->schema[$table])):
            $sql = "SELECT column_name, data_type, is_nullable, column_default, character_maximum_length FROM information_schema.columns WHERE table_name ='{$table}'";
            $columns = $this->fetchAll($sql);

            $schema = array();
            foreach($columns as $column):
                $regex = "({$table}_{$column[column_name]}_seq)";
                $primary = preg_match($regex, $column['column_default'], $o)? 'PRI' : null;
                $schema[$column['column_name']] = array(
                    'type' => $this->column($column['data_type'], $column['character_maximum_length']),
                    'null' => $column['is_nullable'] == 'YES' ? true : false,
                    'default' => $column['column_default'],
                    'key' => $primary,
                );
            endforeach;
            $this->schema[$table] = $schema;
        endif;
        
        return $this->schema[$table];
    }
    protected function column($type, $limit) {
        if($type == 'date'):
            return 'date';
        elseif(in_array($type, array('timestamp with time zone', 'timestamp without time zone'))):
            return 'datetime';
        elseif(in_array($type, array('time with time zone', 'time without time zone'))):
            return 'time';
        elseif(($type == 'smallint' && $limit == 1) || $type == 'boolean'):
            return 'boolean';
        elseif(in_array($type, array('int', 'integer', 'bigint', 'smallint'))):
            return 'integer';
        elseif(strstr($type, 'char') || $type == 'cstring'):
            return 'string';
        elseif(strstr($type, 'text')):
            return 'text';
        elseif(strstr($type, 'blob') || $type == 'binary'):
            return 'binary';
        elseif(in_array($type, array('money', 'double precision', 'real', 'numeric'))):
            return 'float';
        endif;
    }
    public function limit($offset, $limit) {
        if(!is_null($offset)):
            $offset = ' OFFSET ' . $offset;
        endif;
        
        return ' LIMIT ' . $limit . $offset;
    }
    public function count($params) {
        $params['fields'] = array(
            'count' => 'COUNT(' . $this->alias($params['fields']) . ')'
        );
        $results = $this->read($params);
        
        return $results[0]['count'];
    }
    public function renderInsert($params) {
        $sql = 'INSERT INTO ' . $params['table'];
        
        $fields = array_keys($params['values']);
        $sql .= '(' . join(',', $fields) . ')';
        
        $values = rtrim(str_repeat('?,', count($fields)), ',');
        $sql .= ' VALUES(' . $values . ')';
        
        return $sql;
    }
    public function renderSelect($params) {
        $sql = 'SELECT ' . $this->alias($params['fields']);
        $sql .= ' FROM ' . $this->alias($params['table']);
        
        if(is_array($params['joins']) && !empty($params['joins'])):
            foreach($params['joins'] as $join):
                $sql .= ' ' . $this->join($join);
            endforeach;
        elseif(is_string($params['joins'])):
            $sql .= ' ' . $params['joins'];
        endif;
        
        if(!empty($params['conditions'])):
            $sql .= ' WHERE ' . $params['conditions'];
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
            $sql .= $this->limit($params['offset'], $params['limit']);
        endif;

        return $sql;
    }
    public function renderUpdate($params) {
        $params += $this->params;
        $sql = 'UPDATE ' . $this->alias($params['table']) . ' SET ';
        
        $fields = array_keys($params['values']);
        $update_fields = array();
        foreach($fields as $field):
            $update_fields []= $field . ' = ?';
        endforeach;
        $sql .= join(', ', $update_fields);

        if(!empty($params['conditions'])):
            $sql .= ' WHERE ' . $params['conditions'];
        endif;

        if($params['order']):
            $sql .= ' ORDER BY ' . $this->order($params['order']);
        endif;
        
        return $sql;
    }
    public function renderDelete($params) {
        $sql = 'DELETE FROM ' . $this->alias($params['table']);
        
        if(!empty($params['conditions'])):
            $sql .= ' WHERE ' . $params['conditions'];
        endif;

        if($params['order']):
            $sql .= ' ORDER BY ' . $this->order($params['order']);
        endif;

        return $sql;
    }
}