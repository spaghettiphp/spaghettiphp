<?php
/**
 *  Model é o responsável pela camada de dados da aplicação, fazendo a comunicação
 *  com o banco de dados através de uma camada de abstração. Possui funcionalidades
 *  CRUD, além de cuidar dos relacionamentos entre outros models.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Model extends Object {
    /**
     *  Associações do tipo belongsTo.
     */
    public $belongsTo = array();
    /**
     *  Associações do tipo hasMany.
     */
    public $hasMany = array();
    /**
     *  Associações do tipo hasOne.
     */
    public $hasOne = array();
    /**
     *  ID do último registro inserido/alterado.
     */
    public $id = null;
    /**
     *  Nível de recursão padrão de consultas.
     */
    public $recursion = 0;
    /**
     *  Estrutura da tabela do modelo.
     */
    public $schema = array();
    /**
     *  Nome da tabela usada pelo modelo.
     */
    public $table = null;
    /**
     *  Campo de chave primária.
     */
    public $primaryKey = null;
    /**
     *  Configuração de ambiente a ser usada.
     */
    public $environment = null;
    /**
     *  Associações entre modelos disponíveis
     */
    public $associations = array(
        "hasMany" => array("foreignKey", "conditions"),
        "belongsTo" => array("foreignKey", "conditions"),
        "hasOne" => array("foreignKey", "conditions")
    );

    public function __construct() {
        if(is_null($this->environment)):
            $this->environment = Config::read("environment");
        endif;
        if(is_null($this->table)):
            $database = Config::read("database");
            $this->table = $database[$this->environment]["prefix"] . Inflector::underscore(get_class($this));
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
     *  Carrega um modelo.
     *
     *  @param string $model Nome do modelo a ser carregado
     *  @return boolean Verdadeiro se o modelo foi carregado
     */
    public function loadModel($model) {
        if(!isset($this->{$model})):
            if($class =& ClassRegistry::load($model)):
                $this->{$model} = $class;
            else:
                $this->error("missingModel", array("model" => $model));
                return false;
            endif;
        endif;
        return true;
    }
    /**
     *  Gera as associações do modelo.
     *
     *  @return true
     */
    public function createLinks() {
        foreach(array_keys($this->associations) as $type):
            $associations =& $this->{$type};
            foreach($associations as $key => $properties):
                if(is_numeric($key)):
                    unset($associations[$key]);
                    if(is_array($properties)):
                        $associations[$key = $properties["className"]] = $properties;
                    else:
                        $associations[$key = $properties] = array("className" => $properties);
                    endif;
                elseif(!isset($properties["className"])):
                    $associations[$key]["className"] = $key;
                endif;
                $this->loadModel($associations[$key]["className"]);
                $associations[$key] = $this->generateAssociation($type, $associations[$key]);
            endforeach;
        endforeach;
        return true;
    }
    /**
     *  Define os parâmetros padrão para uma associação.
     *
     *  @param string $type Tipo da associação
     *  @param array $association Propriedades da associação
     *  @return array Associação com parâmetros definidos
     */
    public function generateAssociation($type, $association) {
        foreach($this->associations[$type] as $key):
            if(!isset($association[$key])):
                $data = null;
                switch($key):
                    case "foreignKey":
                        if($type == "belongsTo"):
                            $data = Inflector::underscore($association["className"] . "Id");
                        else:
                            $data = Inflector::underscore(get_class($this)) . "_{$this->primaryKey}";
                        endif;
                        break;
                    case "conditions":
                        $data = array();
                endswitch;
                $association[$key] = $data;
            endif;
        endforeach;
        return $association;
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
            array(
                "fields" => array_keys($this->schema),
                "conditions" => array(),
                "order" => null,
                "limit" => null,
                "recursion" => $this->recursion
            ),
            $params
        );
        $results = $db->read($this->table, $params);
        if($params["recursion"] >= 0):
            $results = $this->findDependent($results, $params["recursion"]);
        endif;
        return $results;
    }
    /**
     *  Busca o primiero registro no banco de dados.
     *
     *  @param array $params Parâmetros a serem usados na busca
     *  @return array Resultados da busca
     */
    public function first($params = array()) {
        $params = array_merge(
            array("limit" => 1),
            $params
        );
        $results = $this->all($params);
        return $results[0];
    }
    /**
     *  Busca registros dependentes.
     *
     *  @param array $results Resultados obtidos em uma consulta
     *  @param integer $recursion Nível de recursão
     *  @return void
     */
    public function findDependent($results, $recursion = 0) {
        foreach(array_keys($this->associations) as $type):
            if($recursion < 0 and ($type != "belongsTo" && $recursion <= 0)) continue;
            foreach($this->{$type} as $name => $association):
                foreach($results as $key => $result):
                    $model = $association["className"];
                    $params = array();
                    if($type == "belongsTo"):
                        $params["conditions"] = array(
                            $this->primaryKey => $result[$association["foreignKey"]]
                        );
                        $params["recursion"] = $recursion - 1;
                    else:
                        $params["conditions"] = array(
                            $association["foreignKey"] => $result[$this->primaryKey]
                        );
                        $params["recursion"] = $recursion - 2;
                    endif;
                    $results[$key][$name] = $this->{$model}->all($params);
                endforeach;
            endforeach;
        endforeach;
        return $results;
    }
    public function _findDependent(&$results, $recursion = 0) {
        foreach(array_keys($this->associations) as $type):
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
     *  Conta registros no banco de dados.
     *
     *  @param array $params Parâmetros da busca
     *  @return integer Quantidade de registros encontrados
     */
    public function count($params = array()) {
        $db =& self::getConnection($this->environment);
        $params = array_merge(
            array("fields" => "*", "conditions" => array()),
            $params
        );
        return $db->count($this->table, $params);
    }
    /**
     *  Verifica se um registro existe no banco de dados.
     *
     *  @param integer $id ID do registro a ser verificado
     *  @return boolean Verdadeiro se o registro existe
     */
    public function exists($id) {
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
    public function insert($data) {
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
    public function update($params, $data) {
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
    public function save($data) {
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
    public function delete($id, $dependent = true) {
        $db =& self::getConnection($this->environment);
        $params = array("conditions" => array($this->primaryKey => $id, "limit" => 1));
        if($this->exists($id) && $this->deleteAll($params)):
            if($dependent):
                $this->deleteDependent($id);
            endif;
            return true;
        endif;
        return false;
    }
    /**
     *  Apaga do banco de dados registros dependentes de um registro especificado.
     *
     *  @param integer $id ID do registro principal
     *  @return true
     */
    public function deleteDependent($id) {
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
	/**
	 *  Retorna a quantidade de linhas afetadas pela última consulta.
	 *
	 *  @return integer Quantidade de linhas afetadas
	 */
    public function getAffectedRows() {
        $db =& self::getConnection($this->environment);
        return $db->getAffectedRows();
    }
}

?>