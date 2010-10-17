<?php

require 'lib/core/model/Connection.php';
require 'lib/core/model/Exceptions.php';
require 'lib/core/model/Behavior.php';

class Model extends Hookable {
    public $id;
    protected $primaryKey;
    protected $schema = array();
    protected $table;

    public $associations = array(
        'hasMany' => array('primaryKey', 'foreignKey', 'limit', 'order'),
        'belongsTo' => array('primaryKey', 'foreignKey'),
        'hasOne' => array('primaryKey', 'foreignKey')
    );
    protected $belongsTo = array();
    protected $hasMany = array();
    protected $hasOne = array();

    protected $behaviors = array();

    protected $displayField;

    protected $order;
    protected $limit;
    protected $recursion = 0;

    protected $perPage = 20;
    public $pagination = array();

    protected $validates = array();
    protected $errors = array();

    protected $connection = 'default';
    protected $connected = false;

    //protected $data = array();
    
    protected $beforeSave = array();
    protected $beforeCreate = array();
    protected $beforeUpdate = array();
    protected $beforeDelete = array();
    protected $beforeValidate = array();

    protected $afterSave = array();
    protected $afterCreate = array();
    protected $afterUpdate = array();
    protected $afterDelete = array();
    protected $afterValidate = array();

    protected static $instances = array();

    public function __construct() {
        if(is_null($this->table)):
            $database = Connection::getConfig($this->connection);
            $this->table = $database['prefix'] . Inflector::underscore(get_class($this));
        endif;
        $this->loadBehaviors($this->behaviors);
    }
    public function __call($method, $args) {
        $regex = '/(?P<method>first|all)By(?P<fields>[\w]+)/';
        if(preg_match($regex, $method, $output)):
            $fields = Inflector::underscore($output['fields']);
            $fields = explode('_and_', $fields);

            $conditions = array_slice($args, 0, count($fields));

            $params = array_slice($args, count($fields));
            $params['conditions'] = array_combine($fields, $conditions);

            return $this->$output['method']($params);
        endif;

        throw new BadMethodCallException(get_class($this) . '::' . $method . ' does not exist.');
    }
    public static function load($name) {
        if(!array_key_exists($name, Model::$instances)):
            if(!class_exists($name) && Filesystem::exists('app/models/' . Inflector::underscore($name) . '.php')):
                require_once 'app/models/' . Inflector::underscore($name) . '.php';
            endif;
            if(class_exists($name)):
                Model::$instances[$name] = new $name();
                Model::$instances[$name]->connection();
                Model::$instances[$name]->createLinks();
            else:
                throw new MissingModelException(array(
                    'model' => $name
                ));
            endif;
        endif;

        return Model::$instances[$name];
    }
    /**
     * @todo use static vars
     */
    public function connection() {
        if(!$this->connected):
            $this->connected = true;
            $this->setSource($this->table);
        endif;
        
        return Connection::get($this->connection);
    }
    /**
     * @todo refactor
     */
    public function setSource($table) {
        if($table):
            $db = $this->connection();
            $this->table = $table;
            $sources = $db->listSources();
            if(!in_array($this->table, $sources)):
                throw new MissingTableException(array(
                    'table' => $this->table
                ));
                return false;
            endif;
            if(empty($this->schema)):
                $this->describe();
            endif;
        endif;
        return true;
    }
    public function schema() {
        $this->connection();
        return $this->schema;
    }
    /**
     * @todo refactor
     */
    public function describe() {
        $db = $this->connection();
        $schema = $db->describe($this->table);
        if(is_null($this->primaryKey)):
            foreach($schema as $field => $describe):
                if($describe['key'] == 'PRI'):
                    $this->primaryKey = $field;
                    break;
                endif;
            endforeach;
        endif;
        return $this->schema = $schema;
    }
    public function loadModel($model) {
        return $this->{$model} = Model::load($model);
    }
    public function createLinks() {
        foreach(array_keys($this->associations) as $type):
            $associations =& $this->{$type};
            foreach($associations as $key => $properties):
                if(is_numeric($key)):
                    unset($associations[$key]);
                    if(is_array($properties)):
                        $associations[$key = $properties['className']] = $properties;
                    else:
                        $associations[$key = $properties] = array('className' => $properties);
                    endif;
                elseif(!isset($properties['className'])):
                    $associations[$key]['className'] = $key;
                endif;

                $model = $associations[$key]['className'];
                if(!isset($this->{$model})):
                    $this->loadModel($model);
                endif;

                $associations[$key] = $this->generateAssociation($type, $associations[$key]);
            endforeach;
        endforeach;
        return true;
    }
    public function generateAssociation($type, $association) {
        foreach($this->associations[$type] as $key):
            if(!isset($association[$key])):
                $data = null;
                switch($key):
                    case 'primaryKey':
                        $data = $this->primaryKey;
                        break;
                    case 'foreignKey':
                        if($type == 'belongsTo'):
                            $data = Inflector::underscore($association['className'] . 'Id');
                        else:
                            $data = Inflector::underscore(get_class($this)) . '_' . $this->primaryKey;
                        endif;
                        break;
                    default:
                        $data = null;
                endswitch;
                $association[$key] = $data;
            endif;
        endforeach;
        return $association;
    }

    protected function loadBehaviors($behaviors) {
        foreach($this->behaviors as $key => $behavior):
            $options = array();
            if(!is_numeric($key)):
                $options = $behavior;
                $behavior = $key;
            endif;
            $this->loadBehavior($behavior, $options);
        endforeach;
    }
    protected function loadBehavior($behavior, $options = array()) {
        $behavior = Inflector::camelize($behavior);
        Behavior::load($behavior);
        return $this->{$behavior} = new $behavior($this, $options);
    }
    public function query($query) {
        return $this->connection()->query($query);
    }
    public function fetch($query) {
        return $this->connection()->fetchAll($query);
    }
    public function begin() {
        return $this->connection()->begin();
    }
    public function commit() {
        return $this->connection()->commit();
    }
    public function rollback() {
        return $this->connection()->rollback();
    }
    public function all($params = array()) {
        $db = $this->connection();
        $params += array(
            'table' => $this->table,
            'fields' => '*',
            'order' => $this->order,
            'limit' => $this->limit,
            'recursion' => $this->recursion
        );
        $results = $db->read($params);

        if($params['recursion'] >= 0):
            $results = $this->dependent($results, $params['recursion']);
        endif;

        return $results;
    }
    public function first($params = array()) {
        $params += array(
            'limit' => 1
        );
        $results = $this->all($params);

        return empty($results) ? array() : $results[0];
    }
    public function dependent($results, $recursion = 0) {
        foreach(array_keys($this->associations) as $type):
            if($recursion < 0 and ($type != 'belongsTo' && $recursion <= 0)) continue;
            foreach($this->{$type} as $name => $association):
                foreach($results as $key => $result):
                    $name = Inflector::underscore($name);
                    $model = $association['className'];
                    $params = array();
                    if($type == 'belongsTo'):
                        $params['conditions'] = array(
                            $association["primaryKey"] => $result[$association['foreignKey']]
                        );
                        $params['recursion'] = $recursion - 1;
                    else:
                        $params['conditions'] = array(
                            $association['foreignKey'] => $result[$association["primaryKey"]]
                        );
                        $params['recursion'] = $recursion - 2;
                        if($type == 'hasMany'):
                            $params['limit'] = $association['limit'];
                            $params['order'] = $association['order'];
                        endif;
                    endif;
                    $result = $this->{$model}->all($params);
                    if($type != 'hasMany' && !empty($result)):
                        $result = $result[0];
                    endif;
                    $results[$key][$name] = $result;
                endforeach;
            endforeach;
        endforeach;
        return $results;
    }
    public function count($params = array()) {
        $db = $this->connection();
        $params = array_merge($params, array(
            'fields' => '*',
            'table' => $this->table,
            'offset' => null,
            'limit' => null
        ));
        return $db->count($params);
    }
    public function paginate($params = array()) {
        $params += array(
            'perPage' => $this->perPage,
            'page' => 1
        );

        $params['offset'] = ($params['page'] - 1) * $params['perPage'];
        $params['limit'] = $params['perPage'];

        $totalRecords = $this->count($params);

        $this->pagination = array(
            'totalRecords' => $totalRecords,
            'totalPages' => ceil($totalRecords / $params['perPage']),
            'perPage' => $params['perPage'],
            'offset' => $params['offset'],
            'page' => $params['page']
        );

        return $this->all($params);
    }
    public function toList($params = array()) {
        $params += array(
            'key' => $this->primaryKey,
            'displayField' => $this->displayField,
            'table' => $this->table,
            'order' => $this->order,
            'limit' => $this->limit
        );
        $params['fields'] = array($params['key'], $params['displayField']);

        $all = $this->connection()->read($params);

        $results = array();
        foreach($all as $result):
            $results[$result[$params['key']]] = $result[$params['displayField']];
        endforeach;

        return $results;
    }
    public function exists($conditions) {
        $params = array(
            'conditions' => $conditions
        );
        $row = $this->first($params);

        return !empty($row);
    }
    public function insert($data) {
        $db = $this->connection();
        $params = array(
            'values' => $data,
            'table' => $this->table
        );

        return $db->create($params);
    }
    public function update($params, $data) {
        $db = $this->connection();
        $params += array(
            'values' => $data,
            'table' => $this->table
        );

        return $db->update($params);
    }
    /**
     * @todo refactor
     */
    public function save($data) {
        if(!is_null($this->id)):
            $data[$this->primaryKey] = $this->id;
        endif;

        // apply modified timestamp
        $date = date('Y-m-d H:i:s');
        if(!array_key_exists('modified', $data)):
            $data['modified'] = $date;
        endif;

        $db = $this->connection(); // yes, this is a hack
        // verify if the record exists
        $exists = $this->exists(array(
            $this->primaryKey => $this->id
        ));

        // apply created timestamp
        if(!$exists && !array_key_exists('created', $data)):
            $data['created'] = $date;
        endif;

        // apply beforeSave filter
        $data = $this->fireFilter('beforeSave', $data);
        if(!$data):
            return false;
        endif;

        // filter fields that are not in the schema
        $data = array_intersect_key($data, $this->schema);

        // update a record if it already exists...
        if($exists):
            $save = $this->update(array(
                'conditions' => array(
                    $this->primaryKey => $this->id
                ),
                'limit' => 1
            ), $data);
        // or insert a new one if it doesn't
        else:
            $save = $this->insert($data);
            $this->id = $this->getInsertId();
        endif;

        // fire afterSave action
        $this->fireAction('afterSave');

        return $save;
    }
    /**
     * @todo refactor
     */
    public function validate($data) {
        $this->errors = array();
        $defaults = array(
            'required' => false,
            'allowEmpty' => false,
            'message' => null
        );
        foreach($this->validates as $field => $rules):
            if(!is_array($rules) || (is_array($rules) && isset($rules['rule']))):
                $rules = array($rules);
            endif;
            foreach($rules as $rule):
                if(!is_array($rule)):
                    $rule = array('rule' => $rule);
                endif;
                $rule += $defaults;
                if($rule['allowEmpty'] && empty($data[$field])):
                    continue;
                endif;
                $required = !isset($data[$field]) && $rule['required'];
                if($required):
                    $this->errors[$field] = is_null($rule['message']) ? $rule['rule'] : $rule['message'];
                elseif(isset($data[$field])):
                    if(!$this->callValidationMethod($rule['rule'], $data[$field])):
                        $message = is_null($rule['message']) ? $rule['rule'] : $rule['message'];
                        $this->errors[$field] = $message;
                        break;
                    endif;
                endif;
            endforeach;
        endforeach;
        return empty($this->errors);
    }
    /**
     * @todo refactor
     */
    public function callValidationMethod($params, $value) {
        $method = is_array($params) ? $params[0] : $params;
        $class = method_exists($this, $method) ? $this : 'Validation';
        if(is_array($params)):
            $params[0] = $value;
            return call_user_func_array(array($class, $method), $params);
        else:
            if($class == 'Validation'):
                return Validation::$params($value);
            else:
                return $this->$params($value);
            endif;
        endif;
    }
    public function delete($id, $dependent = true) {
        $params = array(
            'conditions' => array(
                $this->primaryKey => $id
            ),
            'limit' => 1
        );
        if($this->exists(array($this->primaryKey => $id)) && $this->deleteAll($params)):
            if($dependent):
                $this->deleteDependent($id);
            endif;
            return true;
        endif;
        return false;
    }
    public function deleteDependent($id) {
        foreach(array('hasOne', 'hasMany') as $type):
            foreach($this->{$type} as $model => $assoc):
                $this->{$assoc['className']}->deleteAll(array(
                    'conditions' => array(
                        $assoc['foreignKey'] => $id
                    )
                ));
            endforeach;
        endforeach;
        return true;
    }
    public function deleteAll($params = array()) {
        $db = $this->connection();
        $params += array(
            'table' => $this->table,
            'order' => $this->order,
            'limit' => $this->limit
        );
        return $db->delete($params);
    }
    public function getInsertId() {
        return $this->connection()->insertId();
    }
    public function getAffectedRows() {
        return $this->connection()->affectedRows();
    }
    public function escape($value) {
        return $this->connection()->escape($value);
    }
}