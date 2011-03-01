<?php

require_once 'lib/core/model/datasources/PdoDatasource.php';

class MySqlDatasource extends PdoDatasource {
    public function connect($dsn = null, $username = null, $password = null) {
        if(!$this->connection) {
            if(is_null($dsn)) {
                $dsn = $this->dsn();
                $username = $this->config['user'];
                $password = $this->config['password'];
            }

            $this->connection = new PDO($dsn, $username, $password);
            $this->connected = true;
        }

        return $this->connection;
    }

    // @todo add missing DSN elements
    public function dsn() {
        return 'mysql:host=' . $this->config['host'] . ';dbname=' . $this->config['database'];
    }

    public function listSources() {
        if(empty($this->sources)) {
            $query = $this->connection->prepare('SHOW TABLES FROM ' . $this->config['database']);
            $query->setFetchMode(PDO::FETCH_NUM);
            $sources = $query->execute();

            while($source = $query->fetch()) {
                $this->sources []= $source[0];
            }
        }

        return $this->sources;
    }

    public function describe($table) {
        if(!isset($this->schema[$table])) {
            $columns = $this->fetchAll('SHOW COLUMNS FROM ' . $table);
            $schema = array();

            foreach($columns as $column) {
                $schema[$column['Field']] = array(
                    'key' => $column['Key']
                );
            }

            $this->schema[$table] = $schema;
        }

        return $this->schema[$table];
    }

    protected function column($column) {
        preg_match('/([a-z]*)\(?([^\)]*)?\)?/', $column, $type);
        list($column, $type, $limit) = $type;

        if(in_array($type, array('date', 'time', 'datetime', 'timestamp'))) {
            return $type;
        }
        elseif(($type == 'tinyint' && $limit == 1) || $type == 'boolean') {
            return 'boolean';
        }
        elseif(strstr($type, 'int')) {
            return 'integer';
        }
        elseif(strstr($type, 'char') || $type == 'tinytext') {
            return 'string';
        }
        elseif(strstr($type, 'text')) {
            return 'text';
        }
        elseif(strstr($type, 'blob') || $type == 'binary') {
            return 'binary';
        }
        elseif(in_array($type, array('float', 'double', 'real', 'decimal'))) {
            return 'float';
        }
        elseif($type == 'enum' || $type = 'set') {
            return $type . '(' . $limit . ')';
        }
    }

    public function limit($offset, $limit) {
        if(!is_null($offset)) {
            $limit = $offset . ',' . $limit;
        }

        return $limit;
    }

    public function count($params) {
        $fields = '*';

        if(array_key_exists('fields', $params)) {
            $fields = $params['fields'];

            if(is_array($params['fields'])) {
                $fields = $fields[0];
            }
        }

        $params['fields'] = array(
            'count' => 'COUNT(' . $fields . ')'
        );

        $results = $this->read($params)->fetch();

        return $results['count'];
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

        if(is_array($params['joins']) && !empty($params['joins'])) {
            foreach($params['joins'] as $join) {
                $sql .= ' ' . $this->join($join);
            }
        }
        elseif(is_string($params['joins'])) {
            $sql .= ' ' . $params['joins'];
        }

        if(!empty($params['conditions'])) {
            $sql .= ' WHERE ' . $params['conditions'];
        }

        if($params['groupBy']) {
            $sql .= ' GROUP BY ' . $this->alias($params['groupBy']);
        }

        if($params['having']) {
            $sql .= ' HAVING ' . $params['having'];
        }

        if($params['order']) {
            $sql .= ' ORDER BY ' . $this->order($params['order']);
        }

        if($params['offset'] || $params['limit']) {
            $sql .= ' LIMIT ' . $this->limit($params['offset'], $params['limit']);
        }

        return $sql;
    }

    public function renderUpdate($params) {
        $params += $this->params;
        $sql = 'UPDATE ' . $this->alias($params['table']) . ' SET ';

        $fields = array_keys($params['values']);
        $update_fields = array();

        foreach($fields as $field) {
            $update_fields []= $field . ' = ?';
        }

        $sql .= join(', ', $update_fields);

        if(!empty($params['conditions'])) {
            $sql .= ' WHERE ' . $params['conditions'];
        }

        if($params['order']) {
            $sql .= ' ORDER BY ' . $this->order($params['order']);
        }

        if($params['offset'] || $params['limit']) {
            $sql .= ' LIMIT ' . $this->limit($params['offset'], $params['limit']);
        }

        return $sql;
    }

    public function renderDelete($params) {
        $sql = 'DELETE FROM ' . $this->alias($params['table']);

        if(!empty($params['conditions'])) {
            $sql .= ' WHERE ' . $params['conditions'];
        }

        if($params['order']) {
            $sql .= ' ORDER BY ' . $this->order($params['order']);
        }

        if($params['offset'] || $params['limit']) {
            $sql .= ' LIMIT ' . $this->limit($params['offset'], $params['limit']);
        }

        return $sql;
    }
}