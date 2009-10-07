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
     *  Campo a ser usado como título em consultas toList.
     */
    public $displayField = null;
    /**
     *  Configuração de ambiente a ser usada.
     */
    public $environment = null;
    /**
     *  Condições padrão para o modelo.
     */
    public $conditions = array();
    /**
     *  Ordenação padrão para o modelo.
     */
    public $order = null;
    /**
     *  Limite padrão para o modelo.
     */
    public $limit = null;
    /**
     *  Padrão para quantidade de registros por página.
     */
    public $perPage = 20;
    /**
      *  Regras de validação.
      */
    public $validates = array();
    /**
      *  Erros gerados pela última validação.
      */
    public $errors = array();
    /**
     *  Associações entre modelos disponíveis
     */
    public $associations = array(
        "hasMany" => array("foreignKey", "conditions"),
        "belongsTo" => array("foreignKey", "conditions"),
        "hasOne" => array("foreignKey", "conditions")
    );

    public $pagination = array();

    public function __construct() {
        if(is_null($this->environment)):
            $this->environment = Config::read("environment");
        endif;
        if(is_null($this->table)):
            $database = Config::read("database");
            $this->table = $database[$this->environment]["prefix"] . Inflector::underscore(get_class($this));
        endif;
        $this->setSource($this->table);
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
     *  Define a tabela a ser usada pelo modelo.
     *
     *  @param string $table Nome da tabela a ser usada
     *  @return boolean Verdadeiro caso a tabela exista
     */
    public function setSource($table) {
        $db =& self::getConnection($this->environment);
        if($table):
            $this->table = $table;
            $sources = $db->listSources();
            if(!in_array($this->table, $sources)):
                $this->error("missingTable", array("model" => get_class($this), "table" => $this->table));
                return false;
            endif;
            if(empty($this->schema)):
                $this->describe();
            endif;
        endif;
        return true;
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
                "conditions" => $this->conditions,
                "order" => $this->order,
                "limit" => $this->limit,
                "recursion" => $this->recursion
            ),
            $params
        );
        $results = $db->read($this->table, $params);
        if($params["recursion"] >= 0):
            $results = $this->dependent($results, $params["recursion"]);
        endif;
        return $results;
    }
    /**
     *  Busca o primeiro registro no banco de dados.
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
        return empty($results) ? array() : $results[0];
    }
    /**
     *  Busca registros dependentes.
     *
     *  @param array $results Resultados obtidos em uma consulta
     *  @param integer $recursion Nível de recursão
     *  @return array Resultados da busca
     */
    public function dependent($results, $recursion = 0) {
        foreach(array_keys($this->associations) as $type):
            if($recursion < 0 and ($type != "belongsTo" && $recursion <= 0)) continue;
            foreach($this->{$type} as $name => $association):
                foreach($results as $key => $result):
                    $name = Inflector::underscore($name);
                    $model = $association["className"];
                    $params = array();
                    if($type == "belongsTo"):
                        $params["conditions"] = array(
                            $this->primaryKey => $result[$association["foreignKey"]]
                        );
                        $params["recursion"] = $recursion - 1;
                    else:
                        $params["conditions"] = array_merge(
                            $association["conditions"],
                            array(
                                $association["foreignKey"] => $result[$this->primaryKey]
                            )
                        );
                        $params["recursion"] = $recursion - 2;
                    endif;
                    $result = $this->{$model}->all($params);
                    if($type != "hasMany" && !empty($result)):
                        $result = $result[0];
                    endif;
                    $results[$key][$name] = $result;
                endforeach;
            endforeach;
        endforeach;
        return $results;
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
            array("fields" => "*", "conditions" => $this->conditions),
            $params
        );
        return $db->count($this->table, $params);
    }
    /**
     *  Retorna registros paginados.
     *
     *  @param array $params Parâmetros da busca e paginação
     *  @return array Resultados da página $params["page"]
     */
    public function paginate($params = array()) {
        $params = array_merge(
            array(
                "perPage" => $this->perPage,
                "page" => 1
            ),
            $params
        );
        $page = !$params["page"] ? 1 : $params["page"];
        $offset = ($page - 1) * $params["perPage"];
        $params["limit"] = "{$offset},{$params['perPage']}";

        $totalRecords = $this->count($params);
        $this->pagination = array(
            "totalRecords" => $totalRecords,
            "totalPages" => ceil($totalRecords / $params["perPage"]),
            "perPage" => $params["perPage"],
            "offset" => $offset,
            "page" => $page
        );

        return $this->all($params);
    }
    /**
     *  Busca registros no banco de dados em formato de array chave => valor.
     *
     *  @param array $params Parâmetros da busca
     *  @return array Resultados da busca
     */
    public function toList($params = array()) {
        $params = array_merge(
            array(
                "key" => $this->primaryKey,
                "displayField" => $this->displayField
            ),
            $params
        );
        $all = $this->all($params);
        $results = array();
        foreach($all as $result):
            $results[$result[$params["key"]]] = $result[$params["displayField"]];
        endforeach;
        return $results;
    }
    /**
     *  Verifica se um registro existe no banco de dados.
     *
     *  @param integer $id ID do registro a ser verificado
     *  @return boolean Verdadeiro se o registro existe
     */
    public function exists($id) {
        $conditions = array_merge(
            $this->conditions,
            array(
                "conditions" => array(
                    $this->primaryKey => $id
                )
            )
        );
        $row = $this->first($conditions);
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
        if(isset($data[$this->primaryKey]) && !is_null($data[$this->primaryKey])):
            $this->id = $data[$this->primaryKey];
        elseif(!is_null($this->id)):
            $data[$this->primaryKey] = $this->id;
        endif;
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
      *  Valida os dados a serem salvos pelo modelo.
      *
      *  @param array $data Dados a serem validados
      *  @return boolean Verdadeiro caso todos os dados sejam válidos
      */
    public function validate($data) {
        $this->errors = array();
        $defaults = array(
            "required" => false,
            "allowEmpty" => false,
            "message" => null
        );
        foreach($this->validates as $field => $rules):
            if(!is_array($rules) || (is_array($rules) && isset($rules["rule"]))):
                $rules = array($rules);
            endif;
            foreach($rules as $rule):
                if(!is_array($rule)):
                    $rule = array("rule" => $rule);
                endif;
                $rule = array_merge($defaults, $rule);
                $required = !isset($data[$field]) && $rule["required"];
                if($required):
                    $this->errors[$field] = "required";
                elseif(isset($data[$field])):
                    if(!$this->callValidationMethod($rule["rule"], $data[$field])):
                        $message = is_null($rule["message"]) ? $rule["rule"] : $rule["message"];
                        $this->errors[$field] = $message;
                        break;
                    endif;
                endif;
            endforeach;
        endforeach;
        return empty($this->errors);
    }
    /**
      *  Chama um método de validação.
      *
      *  @param mixed $params Nome do método a ser chamado e parâmetros
      *  @param string $value Valor a ser validado
      *  @return boolean Resultado do método de validação
      */
    public function callValidationMethod($params, $value) {
        $method = is_array($params) ? $params[0] : $params;
        $class = method_exists($this, $method) ? $this : "Validation";
        if(is_array($params)):
            $params[0] = $value;
            return call_user_func_array(array($class, $method), $params);
        else:
            if($class == "Validation"):
                return Validation::$params($value);
            else:
                return $this->$params($value);
            endif;
        endif;
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
        $params = array("conditions" => array($this->primaryKey => $id), "limit" => 1);
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
            array("conditions" => $this->conditions, "order" => $this->order, "limit" => $this->limit),
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