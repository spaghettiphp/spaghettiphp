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
        "hasMany" => array("foreignKey", "conditions", "order", "limit"),
        "belongsTo" => array("foreignKey", "conditions"),
        "hasOne" => array("foreignKey", "conditions")
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
            $this->describe();
        endif;
        ClassRegistry::addObject(get_class($this), $this);
        $this->createLinks();
    }
    /**
     *  Chama métodos de atalho para firstBy<field> e allBy<field>.
     *
     *  @param string $method Nome do método chamado
     *  @param array $condition Parmâmetros passados pelo método
     *  @return array Resultado do método, erro caso o método não exista
     */
    public function __call($method, $condition) {
        if(preg_match("/(all|first)By([\w]+)/", $method, $match)):
            $field = Inflector::underscore($match[2]);
            $params = array("conditions" => array($field => $condition[0]));
            if(isset($condition[1])):
                $params = array_merge($params, $condition[1]);
            endif;
            return $this->{$match[1]}($params);
        else:
            trigger_error("Call to undefined method Model::{$method}()", E_USER_ERROR);
            return false;
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
    public function describe() {
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
    /**
     *  Gera as associações do modelo.
     *
     *  @return void
     */
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
                    if($class =& ClassRegistry::load($className)):
                        $this->{$className} = $class;
                    else:
                        $this->error("missingModel", array("model" => $className));
                    endif;
                endif;
                $this->generateAssociation($type);
            endforeach;
        endforeach;
    }
    /**
     *  Define os parâmetros padrão para as associações.
     *
     *  @param string $type Tipo da associação
     *  @return true
     */
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
     *  Inicia uma transação SQL.
     *
     *  @return boolean Verdadeiro se a transação foi iniciada
     */
    public function begin() {
        $db =& self::getConnection($this->environment);
        return $db->begin();
    }
    /**
     *  Completa uma transação SQL.
     *
     *  @return boolean Verdadeiro se a transação foi completada
     */
    public function commit() {
        $db =& self::getConnection($this->environment);
        return $db->commit();
    }
    /**
     *  Cancela uma transação SQL.
     *
     *  @return boolean Verdadeiro se a transação foi cancelada
     */
    public function rollback() {
        $db =& self::getConnection($this->environment);
        return $db->rollback();
    }
    /**
     *  Busca registros no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function all($params = array()) {
        $db =& self::getConnection($this->environment);
        $params = array_merge(
            array("fields" => array_keys($this->schema), "conditions" => array(), "order" => null, "limit" => null, "recursion" => $this->recursion),
            $params
        );
        $results = $db->read($this->table, $params);
        if($params["recursion"] >= 0):
            $this->findDependent($results, $params["recursion"]);
        endif;
        return $results;
    }
    public function first($params = array()) {
        $params = array_merge(
            array("limit" => 1),
            $params
        );
        return $this->all($params);
    }
    /**
     *  Busca registros dependentes.
     *
     *  @param array $results Resultados obtidos em uma consula
     *  @param integer $recursion Nível de recursão
     *  @return void
     */
    public function findDependent(&$results, $recursion = 0) {
        foreach($this->associations as $type):
            if($recursion != 0 || ($type != "hasMany" && $type != "hasOne")):
                foreach($this->{$type} as $name => $assoc):
                    foreach($results as $key => $result):
                        if(isset($this->{$assoc["className"]}->schema[$assoc["foreignKey"]])):
                            $assocCondition = array($assoc["foreignKey"] => $result[$this->primaryKey]);
                        else:
                            $assocCondition = array($this->primaryKey => $result[$assoc["foreignKey"]]);
                        endif;
                        #$attrCondition = isset($conditions[Inflector::underscore($assoc["className"])]) ? $conditions[Inflector::underscore($assoc["className"])] : array();
                        #$condition = array_merge($attrCondition, $assoc["conditions"], $assocCondition);
                        $condition = $assocCondition;
                        $assocRecursion = $type != "belongsTo" ? $recursion - 2 : $recursion - 1;
                        $rows = $this->{$assoc["className"]}->all(array(
                            "conditions" => $condition,
                            "recursion" => $assocRecursion
                        ));
                        $results[$key][Inflector::underscore($name)] = $type == "hasMany" ? $rows : $rows[0];
                    endforeach;
                endforeach;
            endif;
        endforeach;
    }
    /**
     *  Verifica se um registro existe no banco de dados.
     *
     *  @param integer $id ID do registro a ser verificado
     *  @return boolean Verdadeiro se o registro existe
     */
    public function exists($id = null) {
        $row = $this->first(array(
            "conditions" => array(
                $this->primaryKey => $id
            )
        ));
        return !empty($row);
    }
    /**
     *  Insere um registro no banco de dados.
     *
     *  @param array $data Dados a serem inseridos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function insert($data = array()) {
        $db =& self::getConnection($this->environment);
        return $db->create($this->table, $data);
    }
    /**
     *  Atualiza registros no banco de dados.
     *
     *  @param array $params Parâmetros  para os registros a serem atualizados
     *  @param array $data Dados a serem inseridos
     *  @return boolean Verdadeiro se os registros foram atualizado
     */
    public function update($params = array(), $data = array()) {
        $db =& self::getConnection($this->environment);
        $params = array_merge(
            array("conditions" => array(), "order" => null, "limit" => null),
            $params
        );
        return $db->update($this->table, array_merge($params, compact("data")));
    }
    /**
     *  Salva um registro no banco de dados.
     *
     *  @param array $data Dados a serem salvos
     *  @return boolean Verdadeiro se o registro foi salvo
     */
    public function save($data = array()) {
        $this->id = isset($data[$this->primaryKey]) ? $data[$this->primaryKey] : null;
        foreach($data as $field => $value):
            if(!isset($this->schema[$field])):
                unset($data[$field]);
            endif;
        endforeach;
        $date = date("Y-m-d H:i:s");
        if(isset($this->schema["modified"]) && !isset($data["modified"])):
            $data["modified"] = $date;
        endif;
        $exists = $this->exists($this->id);
        if(!$exists && isset($this->schema["created"]) && !isset($data["created"])):
            $data["created"] = $date;
        endif;
        if(!($data = $this->beforeSave($data))) return false;
        if(!is_null($this->id) && $exists):
            $save = $this->update(array(
                "conditions" => array($this->primaryKey => $this->id),
                "limit" => 1
            ), $data);
            $created = false;
        else:
            $save = $this->insert($data);
            $created = true;
            $this->id = $this->getInsertId();
        endif;
        $this->afterSave($created);
        return $save;
    }
    /**
     *  Callback executado antes de salvar um registro.
     *
     *  @param array $data Dados a serem salvos
     *  @return array Dados a serem salvos, falso para cancelar o salvamento
     */
    public function beforeSave($data) {
        return $data;
    }
    /**
     *  Callback executado após salvar um registro.
     *
     *  @param boolean $created Verdadeiro se o registro foi criado
     *  @return boolean Verdadeiro se o registro foi criado, falso se foi atualizado
     */
    public function afterSave($created) {
        return $created;
    }
    /**
     *  Apaga um registro do banco de dados.
     *
     *  @param integer $id ID do registro a ser apagado
     *  @return boolean Verdadeiro caso o registro tenha sido apagado
     */
    public function delete($id = null, $dependent = true) {
        $db =& self::getConnection($this->environment);
        $params = array("conditions" => array($this->primaryKey => $id));
        if($this->exists($id) && $this->deleteAll($params)):
            if($dependent):
                $this->deleteDependent($id);
            endif;
            return true;
        endif;
        return false;
    }
    /**
     *  Short description.
     *
     *  @param integer $id
     *  @return true
     */
    public function deleteDependent($id = null) {
        foreach(array("hasOne", "hasMany") as $type):
            foreach($this->{$type} as $model => $assoc):
                $this->{$assoc["className"]}->deleteAll(array("conditions" => array(
                    $assoc["foreignKey"] => $id
                )));
            endforeach;
        endforeach;
        return true;
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
    /**
     *  Retorna o ID do último registro inserido.
     *
     *  @return integer ID do último registro inserido
     */
    public function getInsertId() {
        $db =& self::getConnection($this->environment);
        return $db->getInsertId();
    }
}

?>