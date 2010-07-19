<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/bootstrap.php';

class MyModel extends AppModel {
    public $behaviors = array('MyBehavior');
    public $table = false;
}

class MyBehavior extends Behavior {
    public $initialized = false;
    public $hooked = false;
    
    public function __construct($model) {
        $this->initialized = true;
        $model->registerHook('dummy', array($this, 'dummyHook'));
    }
    public function dummyHook() {
        $this->hooked = true;
    }
}

class ModelBehaviorsTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->model = new MyModel();
    }
    public function tearDown() {
        $this->model = null;
    }
    
    /**
     * @testdox Model should initialize behaviors
     */
    public function testModelShouldInitializeBehaviors() {
        $this->assertTrue($this->model->MyBehavior->initialized);
    }
    
    /**
     * @testdox Behavior should register hook in Model
     */
    public function testBehaviorShouldRegisterHookInModel() {
        $this->model->fireHook('dummy');
        $this->assertTrue($this->model->MyBehavior->hooked);
    }
}