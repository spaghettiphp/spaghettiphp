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
     *  Associações do tipo Belongs To
     */
    public $belongsTo = array();
    /**
     *  Associações do tipo Has Many
     */
    public $hasMany = array();
    /**
     *  Associações do tipo Has One
     */
    public $hasOne = array();
    /**
     *  Dados do registro
     */
    public $data = array();
    /**
     *  ID do registro
     */
    public $id = null;
    /**
     *  Nível de recursão padrão das consultas find
     */
    public $recursion = 0;
    /**
     *  Descrição da tabela do modelo
     */
    public $schema = array();
    /**
     *  Nome da tabela usada pelo modelo
     */
    public $table = null;
    /**
     *  ID do último registro inserido
     */
    public $insertId = null;
    /**
     *  Registros afetados pela consulta
     */
    public $affectedRows = null;
    /**
     *  Campo de chave primária.
     */
    public $primaryKey = null;
    /**
     *  Configuração de ambiente a ser usada.
     */
    public $environment = null;

    public function __construct($table = null) {
        if(is_null($this->table)):
            if(!is_null($table)):
                $this->table = $table;
            else:
                $database = Config::read("database");
                $environment = is_null($this->environment) ? Config::read("environment") : $this->environment;
                $this->table = $database[$environment]["prefix"] . Inflector::underscore(get_class($this));
            endif;
        endif;
        if($this->table && empty($this->schema)):
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
    /**
     *  Retorna o datasource em uso.
     *
     *  @return object Datasource em uso
     */
    public static function &getConnection($environment = null) {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] = Connection::getDatasource($environment);
        endif;
        return $instance[0];
    }
    /**
     *  Descreve a tabela do banco de dados.
     *
     *  @return array Descrição da tabela do banco de dados
     */
    public function describeTable() {
        $db =& self::getConnection($this->environment);
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
            $associations =& $this->{$type};
            foreach($associations as $key => $assoc):
                if(is_numeric($key)):
                    $key = array_unset($associations, $key);
                    if(is_array($assoc)):
                        $associations[$key["className"]] = $key;
                    else:
                        $associations[$key] = array("className" => $key);
                    endif;
                elseif(!isset($assoc["className"])):
                    $associations[$key]["className"] = $key;
                endif;
                $className = $associations[$key]["className"];
                if(!isset($this->{$className})):
                    $this->{$className} = ClassRegistry::init($className);
                endif;
                $this->generateAssociation($type);
            endforeach;
        endforeach;
    }
    public function generateAssociation($type) {
        $associations =& $this->{$type};
        foreach($associations as $k => $assoc):
            foreach($this->associationKeys[$type] as $key):
                if(!isset($assoc[$key])):
                    $data = null;
                    switch($key):
                        case "foreignKey":
                            $data = ($type == "belongsTo") ? Inflector::underscore($class . "Id") : Inflector::underscore(get_class($this)) . "_{$this->primaryKey}";
                            break;
                        case "conditions":
                            $data = array();
                    endswitch;
                    $associations[$k][$key] = $data;
                endif;
            endforeach;
        endforeach;
        return true;
    }
    /**
     *  Executa uma consulta diretamente no datasource.
     *
     *  @param string $query Consulta a ser executada
     *  @return mixed Resultado da consulta
     */
    public function query($query) {
        $db =& self::getConnection($this->environment);
        return $db->query($query);
    }


    /**
     *  Short description.
     *
     *  @param string $type
     *  @param array $params
     *  @return array
     */
    public function find($type = "all", $params = array()) {
        $db =& self::getConnection($this->environment);
        return $db->renderSql("select", array(
            "table" => $this->table,
            "fields" => $params["fields"]
        ));
    }
    /**
     *  Apaga um registro do banco de dados.
     *
     *  @param integer $id ID do registro a ser apagado
     *  @return boolean Verdadeiro caso o registro tenha sido apagado
     */
    public function delete($id = null) {
        $db =& self::getConnection($this->environment);
        $params = array("conditions" => array(array($this->primaryKey => $id)));
        if($this->exists($id) && $this->deleteAll($params)):
            return true;
        endif;
        return false;
    }
    /**
     *  Apaga registros do banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na operação
     *  @return boolean Verdadeiro caso os registros tenham sido apagados.
     */
    public function deleteAll($params = array()) {
        $db =& self::getConnection($this->environment);
        $params = array_merge(
            array("conditions" => array(), "order" => null, "limit" => null),
            $params
        );
        return $db->delete($this->table, $params);
    }
    
    
    public function findAll($conditions = array(), $order = null, $limit = null, $recursion = null) {
        $db =& self::getConnection($this->environment);
        $recursion = pick($recursion, $this->recursion);
        $results = $db->fetchAll($db->sqlQuery($this->table, "select", $conditions, null, $order, $limit));
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
        $db =& self::getConnection($this->environment);
        if($this->query($db->sqlQuery($this->table, "update", $conditions, $data))):
            $this->affectedRows = mysql_affected_rows();
            return true;
        endif;
        return false;
    }
    public function insert($data = array()) {
        $db =& self::getConnection($this->environment);
        if($this->query($db->sqlQuery($this->table, "insert", $data))):
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
        $row = $this->findAllById($id);
        if(!empty($row)):
            return true;
        endif;
        return false;
    }
    public function getInsertId() {
        return $this->insertId;
    }
    public function getAffectedRows() {
        return $this->affectedRows;
    }
}

?>