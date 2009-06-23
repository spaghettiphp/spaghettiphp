<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Model extends Object {
    /**
     * Associações entre modelos disponíveis
     */
    public $associations = array("hasMany", "belongsTo", "hasOne");
    /**
     * Chaves disponíveis para cada associação
     */
    public $associationKeys = array(
        "hasMany" => array("className", "foreignKey", "conditions", "order", "limit", "dependent"),
        "belongsTo" => array("className", "foreignKey", "conditions"),
        "hasOne" => array("className", "foreignKey", "conditions", "dependent")
    );
    /**
     * Associações do tipo Belongs To
     */
    public $belongsTo = array();
    /**
     * Associações do tipo Has Many
     */
    public $hasMany = array();
    /**
     * Associações do tipo Has One
     */
    public $hasOne = array();
    /**
     * Dados do registro
     */
    public $data = array();
    /**
     * ID do registro
     */
    public $id = null;
    /**
     * Nível de recursão padrão das consultas find
     */
    public $recursion = 0;
    /**
     * Descrição da tabela do modelo
     */
    public $schema = array();
    /**
     * Nome da tabela usada pelo modelo
     */
    public $table = null;
    /**
     * ID do último registro inserido
     */
    public $insertId = null;
    /**
     * Registros afetados pela consulta
     */
    public $affectedRows = null;
    /**
     *  Campo de chave primária.
     */
    public $primaryKey = null;

    public function __construct($table = null) {
        if(is_null($this->table)):
            if(!is_null($table)):
                $this->table = $table;
            else:
                $database = Config::read("database");
                $this->table = $database["prefix"] . Inflector::underscore(get_class($this));
            endif;
        endif;
        
        if($this->table !== false):
            $this->describeTable();
        endif;
        
        ClassRegistry::addObject(get_class($this), $this);
        $this->createLinks();
    }
    public function __call($method, $params) {
        $params = array_merge($params, array(null, null, null, null, null));
        if(preg_match("/findAllBy(.*)/", $method, $field)):
            $field[1] = Inflector::underscore($field[1]);
            return $this->findAllBy($field[1], $params[0], $params[1], $params[2], $params[3], $params[4]);
        elseif(preg_match("/findBy(.*)/", $method, $field)):
            $field[1] = Inflector::underscore($field[1]);
            return $this->findBy($field[1], $params[0], $params[1], $params[2], $params[3]);
        endif;
    }
    public function __set($field, $value = "") {
        if(isset($this->schema[$field])):
            $this->data[$field] = $value;
        elseif(is_subclass_of($value, "Model")):
            $this->{$field} = $value;
        endif;
    }
    public function __get($field) {
        if(isset($this->schema[$field])):
            return $this->data[$field];
        endif;
        return null;
    }
    /**
     *  Retorna o datasource em uso.
     *
     *  @return object Datasource em uso
     */
    public static function &getConnection() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] = Connection::getDatasource();
        endif;
        return $instance[0];
    }
    public function beforeSave() {
        return true;
    }
    public function afterSave() {
        return true;
    }
    /**
     *  Descreve a tabela do banco de dados.
     *
     *  @return array Descrição da tabela do banco de dados
     */
    public function describeTable() {
        $db =& self::getConnection();
        $schema = $db->describe($this->table);
        if(is_null($this->primaryKey)):
            foreach($schema as $field => $describe):
                if($describe["key"] == "PRI"):
                    $this->primaryKey = $field;
                    break;
                endif;
            endforeach;
        endif;
        return $this->schema = $schema;
    }
    public function createLinks() {
        foreach($this->associations as $type):
            $associationType = $this->{$type};
            foreach($associationType as $key => $assoc):
                if(is_numeric($key)):
                    $class = "";
                    $data = array();
                    unset($this->{$type}[$key]);
                    if(is_array($assoc)):
                        $data = $assoc;
                        $assoc = $assoc["className"];
                    endif;
                    $this->{$type}[$assoc] = $data;
                else:
                    $assoc = $key;
                endif;
                if(!isset($this->{$assoc})):
                    $this->{$assoc} = ClassRegistry::init("$assoc");
                endif;
            endforeach;
            $this->generateAssociation($type);
        endforeach;
    }
    public function generateAssociation($type) {
        foreach($this->{$type} as $class => $assoc):
            foreach($this->associationKeys[$type] as $key):
                if(!isset($this->{$type}[$class][$key]) || $this->{$type}[$class][$key] === null):
                    $data = null;
                    switch($key):
                        case "className":
                            $data = $class;
                            break;
                        case "foreignKey":
                            $data = ($type == "belongsTo") ? Inflector::underscore($class . "Id") : Inflector::underscore(get_class($this)) . "_{$this->primaryKey}";
                            break;
                        case "conditions":
                            $data = array();
                            break;
                        case "dependent":
                            $data = true;
                            break;
                    endswitch;
                    $this->{$type}[$class][$key] = $data;
                endif;
            endforeach;
        endforeach;
        return $this->{$type};
    }
    public function sqlQuery($type = "select", $parameters = array(), $values = array(), $order = null, $limit = null, $flags = null) {
        $params = $this->sqlConditions($parameters);
        $values = $this->sqlConditions($values);
        if(is_array($order)):
            $orders = "";
            foreach($order as $key => $value):
                if(!is_numeric($key)):
                    $value = "{$key} {$value}";
                endif;
                $orders .= "{$value},";
            endforeach;
            $order = trim($orders, ",");
        endif;
        if(is_array($flags)):
            $flags = join(" ", $flags);
        endif;
        $types = array(
            "delete" => "DELETE" . if_string($flags, " {$flags}") . " FROM {$this->table}" . if_string($params, " WHERE {$params}") . if_string($order, " ORDER BY {$order}") . if_string($limit, " LIMIT {$limit}"),
            "insert" => "INSERT" . if_string($flags, " {$flags}") . " INTO {$this->table} SET " . $this->sqlSet($params),
            "replace" => "REPLACE" . if_string($flags, " {$flags}") . " INTO {$this->table}" . if_string($params, " SET {$params}"),
            "select" => "SELECT" . if_string($flags, " {$flags}") . " * FROM {$this->table}" . if_string($params, " WHERE {$params}") . if_string($order, " ORDER BY {$order}") . if_string($limit, " LIMIT {$limit}"),
            "truncate" => "TRUNCATE TABLE {$this->table}",
            "update" => "UPDATE" . if_string($flags, " {$flags}") . " {$this->table} SET " . $this->sqlSet($values) . if_string($params, " WHERE {$params}") . if_string($order, " ORDER BY {$order}") . if_string($limit, " LIMIT {$limit}"),
            "describe" => "DESCRIBE {$this->table}"
        );
        
        return $types[$type];
    }
    public function sqlSet($data = "") {
        return preg_replace("/' AND /", "', ", $data);
    }
    public function sqlConditions($conditions) {
        $sql = "";
        $logic = array("or", "or not", "||", "xor", "and", "and not", "&&", "not");
        $comparison = array("=", "<>", "!=", "<=", "<", ">=", ">", "<=>", "LIKE");
        if(is_array($conditions)):
            foreach($conditions as $field => $value):
                if(is_string($value) && is_numeric($field)):
                    $sql .= "{$value} AND ";
                elseif(is_array($value)):
                    if(is_numeric($field)):
                        $field = "OR";
                    elseif(in_array($field, $logic)):
                        $field = strtoupper($field);
                    elseif(preg_match("/([a-z]*) BETWEEN/", $field, $parts) && $this->schema[$parts[1]]):
                        $sql .= "{$field} '" . join("' AND '", $value) . "'";
                        continue;
                    else:
                        $values = array();
                        foreach($value as $item):
                            $values []= $this->sqlConditions(array($field => $item));
                        endforeach;
                        $sql .= "(" . join(" OR ", $values) . ") AND ";
                        continue;
                    endif;
                    $sql .= preg_replace("/' AND /", "' {$field} ", $this->sqlConditions($value));
                else:
                    if(preg_match("/([a-z]*) (" . join("|", $comparison) . ")/", $field, $parts) && $this->schema[$parts[1]]):
                        $value = $this->escape($value);
                        $sql .= "{$parts[1]} {$parts[2]} '{$value}' AND ";
                    elseif($this->schema[$field]):
                        $value = $this->escape($value);
                        $sql .= "{$field} = '{$value}' AND ";
                    endif;
                endif;
            endforeach;
            $sql = trim($sql, " AND ");
        else:
            $sql = $conditions;
        endif;
        return $sql;
    }
    /**
     *  Executa uma consulta diretamente no datasource.
     *
     *  @param string $query Consulta a ser executada
     *  @return mixed Resultado da consulta
     */
    public function query($query) {
        $db =& self::getConnection();
        return $db->query($query);
    }
    public function fetchResults($query) {
        $results = array();
        if($query = $this->query($query)):
            while($row = mysql_fetch_assoc($query)):
                $results []= $row;
            endwhile;
        endif;
        return $results;
    }
    public function findAll($conditions = array(), $order = null, $limit = null, $recursion = null) {
        $recursion = pick($recursion, $this->recursion);
        $results = $this->fetchResults($this->sqlQuery("select", $conditions, null, $order, $limit));
        if($recursion >= 0):
            foreach($this->associations as $type):
                if($recursion != 0 || ($type != "hasMany" && $type != "hasOne")):
                    foreach($this->{$type} as $assoc):
                        foreach($results as $key => $result):
                            if(isset($this->{$assoc["className"]}->schema[$assoc["foreignKey"]])):
                                $assocCondition = array($assoc["foreignKey"] => $result[$this->primaryKey]);
                            else:
                                $assocCondition = array($this->primaryKey => $result[$assoc["foreignKey"]]);
                            endif;
                            $attrCondition = isset($conditions[Inflector::underscore($assoc["className"])]) ? $conditions[Inflector::underscore($assoc["className"])] : array();
                            $condition = array_merge($attrCondition, $assoc["conditions"], $assocCondition);
                            $assocRecursion = $type != "belongsTo" ? $recursion - 2 : $recursion - 1;
                            $rows = $this->{$assoc["className"]}->findAll($condition, null, null, $assocRecursion);
                            $results[$key][Inflector::underscore($assoc["className"])] = $type == "hasMany" ? $rows : $rows[0];
                        endforeach;
                    endforeach;
                endif;
            endforeach;
        endif;
        return $results;
    }
    public function findAllBy($field = "id", $value = null, $conditions = array(), $order = null, $limit = null, $recursion = null) {
        if(!is_array($conditions)) $conditions = array();
        $conditions = array_merge(array($field => $value), $conditions);
        return $this->findAll($conditions, $order, $limit, $recursion);
    }
    public function find($conditions = array(), $order = null, $recursion = null) {
        $results = $this->findAll($conditions, $order, 1, $recursion);
        return empty($results) ? array() : $results[0];
    }
    public function findBy($field = "id", $value = null, $conditions = array(), $order = null, $recursion = null) {
        if(!is_array($conditions)) $conditions = array();
        $conditions = array_merge(array($field => $value), $conditions);
        return $this->find($conditions, $order, $recursion);
    }
    public function create() {
        $this->id = null;
        $this->data = array();
    }
    public function read($id = null, $recursion = null) {
        if($id != null):
            $this->id = $id;
        endif;
        $this->data = $this->find(array($this->primaryKey => $this->id), null, $recursion);
        return $this->data;
    }
    public function update($conditions = array(), $data = array()) {
        if($this->query($this->sqlQuery("update", $conditions, $data))):
            $this->affectedRows = mysql_affected_rows();
            return true;
        endif;
        return false;
    }
    public function insert($data = array()) {
        if($this->query($this->sqlQuery("insert", $data))):
            $this->insertId = mysql_insert_id();
            $this->affectedRows = mysql_affected_rows();
            return true;
        endif;
        return false;
    }
    public function save($data = array()) {
        if(empty($data)):
            $data = $this->data;
        endif;

        if(isset($this->schema["modified"]) && $this->schema["modified"]["type"] == "datetime" && !isset($data["modified"])):
            $data["modified"] = date("Y-m-d H:i:s");
        endif;
        
        $this->beforeSave();
        if(isset($data[$this->primaryKey]) && $this->exists($data[$this->primaryKey])):
            $this->update(array($this->primaryKey => $data[$this->primaryKey]), $data);
            $this->id = $data[$this->primaryKey];
        else:
            if(isset($this->schema["created"]) && $this->schema["created"]["type"] == "datetime" && !isset($data["created"])):
                $data["created"] = date("Y-m-d H:i:s");
            endif;
            $this->insert($data);
            $this->id = $this->get_insert_id();
        endif;
        $this->afterSave();
        
        foreach(array("hasOne", "hasMany") as $type):
            foreach($this->{$type} as $class => $assoc):
                $assocModel = Inflector::underscore($class);
                if(isset($data[$assocModel])):
                    $this->beforeSave();
                    $data[$assocModel][$assoc["foreignKey"]] = $this->id;
                    $this->{$class}->save($data[$assocModel]);
                    $this->afterSave();
                endif;
            endforeach;
        endforeach;
        
        return $this->data = $this->read($this->id);
    }
    public function saveAll($data) {
        if(isset($data[0]) && is_array($data[0])):
            foreach($data as $row):
                $this->save($row);
            endforeach;
        else:
            return $this->save($data);
        endif;
        return true;
    }
    public function exists($id = null) {
        $method = "findBy" . Inflector::camelize($this->primaryKey);
        $row = $this->$method($id);
        if(!empty($row)):
            return true;
        endif;
        return false;
    }
    public function deleteAll($conditions = array(), $order = null, $limit = null) {
        if($this->query($this->sqlQuery("delete", $conditions, null, $order, $limit))):
            $this->affectedRows = mysql_affected_rows();
            return true;
        endif;
        return false;
    }
    /**
     * O método Model::delete() exclui um registro da tabela do modelo, de acordo com
     * o ID passado como parâmetro, e seus dependentes em associações de modelos, se
     * houverem.
     * 
     * @return
     */
    public function delete($id = null, $dependent = false) {
        $return = $this->deleteAll(array($this->primaryKey => $id), null, 1);
        if($dependent):
            foreach(array("hasMany", "hasOne") as $type):
                foreach($this->{$type} as $model => $assoc):
                    if($assoc["dependent"]):
                        $this->{$model}->deleteAll(array(
                            $assoc["foreignKey"] => $id
                        ));
                    endif;
                endforeach;
            endforeach;
        endif;
        return $return;
    }
    /**
     * O método Model::get_insert_id() retorna o ID do último registro inserido
     * na tabela do modelo.
     *
     * @return integer
     */
    public function getInsertId() {
        return $this->insertId;
    }
    /**
     * O método Model::get_affected_rows() retorna o número de registros afetados
     * por uma consulta.
     */
    public function getAffectedRows() {
        return $this->affectedRows;
    }
    /**
     * O método Model::escape() prepara dados para uso em consultas SQL, retirando
     * caracteres que possam ser perigosos, evitando possíveis ataques de SQL Injection.
     */
    public function escape($data) {
        if(get_magic_quotes_gpc()):
            $data = stripslashes($data);
        endif;
        return $data; #mysql_real_escape_string($data, Model::getConnection());
    }
}
?>