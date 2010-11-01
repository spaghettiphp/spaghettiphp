<?php

require 'lib/core/model/Connection.php';
require 'lib/core/model/Table.php';
require 'lib/core/model/Exceptions.php';
require 'lib/core/model/Behavior.php';

class Model extends Hookable {
    public $id;

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
    protected $table;
    protected $connection = 'default';

    protected $order;
    protected $limit;
    protected $recursion = 0;

    protected $perPage = 20;
    protected $pagination = array();

    protected $validates = array();
    protected $errors = array();

    protected $data = array();
    
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

    protected $_models = array();
    protected $_behaviors = array();

    protected static $instances = array();

    public function __construct($data = null) {
        if(!is_null($data)) {
            $this->id = $data['id'];
            $this->data = $data;
        }
        $this->loadBehaviors($this->behaviors);
    }
    
    public function __call($method, $args) {
        $regex = '/(?P<method>first|all)By(?P<fields>[\w]+)/';
        if(preg_match($regex, $method, $output)) {
            $fields = Inflector::underscore($output['fields']);
            $fields = explode('_and_', $fields);

            $conditions = array_slice($args, 0, count($fields));

            $params = array_slice($args, count($fields));
            $params['conditions'] = array_combine($fields, $conditions);

            return $this->$output['method']($params);
        }

        throw new BadMethodCallException(get_class($this) . '::' . $method . ' does not exist.');
    }

    public function __set($name, $value) {
        // @todo shouldn't fail silently
        $this->data[$name] = $value;
    }
    
    public function __get($name) {
        $attrs = array('data', '_models', '_behaviors');
        
        foreach($attrs as $attr) {
            if(array_key_exists($name, $this->{$attr})) {
                return $this->{$attr}[$name];
            }
        }
        
        throw new RuntimeException(get_class($this) . '->' . $name . ' does not exist.');
    }
    
    public static function load($name) {
        if(!array_key_exists($name, Model::$instances)) {
            $filename = 'app/models/' . Inflector::underscore($name) . '.php';
            if(!class_exists($name) && Filesystem::exists($filename)) {
                require_once $filename;
            }

            if(class_exists($name)) {
                Model::$instances[$name] = new $name();
                Model::$instances[$name]->createLinks();
            }
            else {
                throw new MissingModelException(array(
                    'model' => $name
                ));
            }
        }

        return Model::$instances[$name];
    }

    public function getConnection() {
        return $this->connection;
    }

    public function connection() {
        return Table::load($this)->connection();
    }

    protected function table() {
        return Table::load($this)->name();
    }

    public function getTable() {
        return $this->table;
    }
    
    public function schema() {
        return Table::load($this)->schema();
    }

    public function primaryKey() {
        return Table::load($this)->primaryKey();
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
                if(!array_key_exists($model, $this->_models)) {
                    $this->_models[$model] = Model::load($model);
                }

                $associations[$key] = $this->generateAssociation($type, $associations[$key]);
            endforeach;
        endforeach;
    }
    
    public function generateAssociation($type, $association) {
        foreach($this->associations[$type] as $key):
            if(!isset($association[$key])):
                $data = null;
                switch($key):
                    case 'primaryKey':
                        $data = $this->primaryKey();
                        break;
                    case 'foreignKey':
                        if($type == 'belongsTo'):
                            $data = Inflector::underscore($association['className'] . 'Id');
                        else:
                            $data = Inflector::underscore(get_class($this)) . '_' . $this->primaryKey();
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
        return $this->_behaviors[$behavior] = new $behavior($this, $options);
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

    public function insertId() {
        return $this->connection()->insertId();
    }
    
    public function affectedRows() {
        return $this->connection()->affectedRows();
    }
    
    public function escape($value) {
        return $this->connection()->escape($value);
    }
    
    public function all($params = array()) {
        $params += array(
            'table' => $this->table(),
            'order' => $this->order,
            'limit' => $this->limit,
            'recursion' => $this->recursion,
            'orm' => false
        );

        $query = $this->connection()->read($params);

        $results = array();
        while($result = $query->fetch()) {
            if($params['orm']) {
                $self = get_class($this);
                $results []= new $self($result);
            }
            else {
                $results []= $result;
            }
        }

        if(!$params['orm'] && $params['recursion'] >= 0) {
            $results = $this->dependent($results, $params['recursion']);
        }
        
        return $results;
    }
    
    public function first($params = array()) {
        $params['limit'] = 1;
        $results = $this->all($params);

        return empty($results) ? null : $results[0];
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
                            $association['primaryKey'] => $result[$association['foreignKey']]
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
                    $result = $this->_models[$model]->all($params);
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
            'table' => $this->table(),
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

        $count = $this->count($params);

        $this->pagination = array(
            'totalRecords' => $count,
            'totalPages' => ceil($count / $params['perPage']),
            'perPage' => $params['perPage'],
            'offset' => $offset,
            'page' => $params['page']
        );

        return $this->all($params);
    }
    
    public function toList($params = array()) {
        $params += array(
            'key' => $this->primaryKey(),
            'displayField' => $this->displayField,
            'table' => $this->table(),
            'order' => $this->order,
            'limit' => $this->limit
        );
        
        if(!array_key_exists('fields', $params)) {
            $params['fields'] = array_merge(
                (array) $params['key'],
                (array) $params['displayField']
            );
        }

        $all = $this->connection()->read($params);

        $results = array();
        while($result = $all->fetch()) {
            if(is_array($params['displayField'])) {
                $keys = array_flip($params['displayField']);
                $value = array_intersect_key($result, $keys);
            }
            else {
                $value = $result[$params['displayField']];
            }
            
            $results[$result[$params['key']]] = $value;
        }

        return $results;
    }
    
    public function exists($conditions) {
        return (bool) $this->count(array(
            'conditions' => $conditions
        ));
    }
    
    public function insert($data) {
        $params = array(
            'values' => $data,
            'table' => $this->table()
        );

        return $this->connection()->create($params);
    }
    
    public function update($params, $data) {
        $params += array(
            'values' => $data,
            'table' => $this->table()
        );

        return $this->connection()->update($params);
    }

    public function save($data = array()) {
        if(!empty($data)) {
            $this->data = $data;
        }
        
        if(!is_null($this->id)):
            $this->data[$this->primaryKey()] = $this->id;
        endif;

        // apply modified timestamp
        $date = date('Y-m-d H:i:s');
        if(!array_key_exists('modified', $this->data)):
            $this->data['modified'] = $date;
        endif;

        // verify if the record exists
        $exists = $this->exists(array(
            $this->primaryKey() => $this->id
        ));

        // apply created timestamp
        if(!$exists && !array_key_exists('created', $this->data)):
            $this->data['created'] = $date;
        endif;

        // apply beforeSave filter
        $this->data = $this->fireFilter('beforeSave', $this->data);
        if(!$this->data):
            return false;
        endif;

        // filter fields that are not in the schema
        $data = array_intersect_key($this->data, $this->schema());

        // update a record if it already exists...
        if($exists):
            $save = $this->update(array(
                'conditions' => array(
                    $this->primaryKey() => $this->id
                ),
                'limit' => 1
            ), $data);
        // or insert a new one if it doesn't
        else:
            $save = $this->insert($data);
            $this->id = $this->insertId();
        endif;

        // fire afterSave action
        $this->fireAction('afterSave');

        return $save;
    }

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
                $this->primaryKey() => $id
            ),
            'limit' => 1
        );
        if($this->exists(array($this->primaryKey() => $id)) && $this->deleteAll($params)):
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
            'table' => $this->table(),
            'order' => $this->order,
            'limit' => $this->limit
        );
        return $db->delete($params);
    }
}