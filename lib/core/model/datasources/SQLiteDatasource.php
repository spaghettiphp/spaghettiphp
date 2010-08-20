<?php

require_once 'lib/core/model/datasources/PdoDatasource.php';

class SQLiteDatasource extends PdoDatasource {
    public function connect($dsn = null, $username = null, $password = null) {
        if(!$this->connection):
            if(is_null($dsn)):
                $dsn = $this->dsn();
            endif;
            $this->connection = new PDO($dsn);
            $this->connected = true;
        endif;
        
        return $this->connection;
    }
    // @todo add missing DSN elements
    public function dsn() {
        return 'sqlite:' . Filesystem::path($this->config['path']);
    }
    public function listSources() {
        if(empty($this->sources)):
            $query = $this->connection->prepare('SELECT tbl_name FROM sqlite_master');
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
            $columns = $this->fetchAll("PRAGMA table_info('{$table}')");
            $schema = array();
            foreach($columns as $column):
                $extra = $column['pk']== 1 && stripos($column['type'], 'INTEGER') === 0 ?
                            'auto_increment' : null;
                            
                $schema[$column['name']] = array(
                    'type' => $this->column($column['type']),
                    'null' => $column['notnull'] == '0' ? false : true,
                    'default' => $column['dflt_value'],
                    'key' => ($column['pk'] == 1) ? 'PRI' : null,
                    'extra' => $extra
                );
            endforeach;
            $this->schema[$table] = $schema;
        endif;
        
        return $this->schema[$table];
    }
    protected function column($column) {
        //pr($column);
        preg_match('/([a-zA-Z]*)\(?([^\)]*)?\)?/', $column, $type);
        //pr($type);
        list($column, $type, $limit) = $type;
        $type = strtolower($type);
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
    public function limit($offset, $limit) {
        if(!is_null($offset)):
            $limit = $offset . ',' . $limit;
        endif;
        
        return $limit;
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
            $sql .= ' LIMIT ' . $this->limit($params['offset'], $params['limit']);
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