<?php

class Table {
    protected $primaryKey;
    protected $schema;
    protected $table;
    protected $model;
    protected $connection;
    protected $connected = false;
    protected static $cache = array();

    public function __construct($connection, $model) {
        $this->connection = $connection;
        $this->model = $model;
    }

    public static function load($model) {
        $model_name = get_class($model);
        $connection = $model->getConnection();
        $name = $connection . '.' . $model_name;

        if(!array_key_exists($name, self::$cache)) {
            self::$cache[$name] = new self($connection, $model);
        }

        return self::$cache[$name];
    }

    public function connection() {
        return Connection::get($this->connection);
    }

    public function name() {
        if(is_null($this->table)) {
            $this->table = $this->model->getTable();

            if(is_null($this->table)) {
                $database = Connection::config($this->connection);
                $this->table = $database['prefix'] . Inflector::underscore(get_class($this->model));
            }
        }

        return $this->table;
    }

    public function schema() {
        if($this->name() && is_null($this->schema)) {
            $db = $this->connection();
            $sources = $db->listSources();
            if(!in_array($this->table, $sources)) {
                throw new MissingTableException($this->table . ' could not be founded on ' . $this->connection . '.');
                return false;
            }

            if(empty($this->schema)) {
                $this->describe();
            }
        }

        return $this->schema;
    }

    public function primaryKey() {
        if($this->name() && $this->schema()) {
            return $this->primaryKey;
        }
    }

    protected function describe() {
        $db = $this->connection();
        $schema = $db->describe($this->table);
        if(is_null($this->primaryKey)) {
            foreach($schema as $field => $describe) {
                if($describe['key'] == 'PRI') {
                    $this->primaryKey = $field;
                    break;
                }
            }
        }

        return $this->schema = $schema;
    }
}