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
    public $hookedFirst = false;
    public $hookedSecond = false;
    public $param = false;
    protected $actions = array(
        'dummy' => 'dummy',
        'double' => array('first', 'second'),
        'parameter' => 'parameter',
        'exception' => 'exception'
    );
    protected $filters = array(
        'propagate' => 'propagate',
        'stop' => array('propagate', 'stop', 'true')
    );
    
    public function __construct($model) {
        parent::__construct($model);
        $this->initialized = true;
    }
    public function dummy() {
        $this->hooked = true;
    }
    public function first() {
        $this->hookedFirst = true;
    }
    public function second() {
        $this->hookedSecond = true;
    }
    public function parameter($param) {
        $this->param = $param;
    }
    public function propagate($param) {
        return $param;
    }
    public function stop($param) {
        return false;
    }
    public function true($param) {
        return true;
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
     * @testdox Behavior should register action in Model
     */
    public function testBehaviorShouldRegisterActionInModel() {
        $this->model->fireAction('dummy');
        $this->assertTrue($this->model->MyBehavior->hooked);
    }

    /**
     * @testdox Model should fire more than one action if available
     */
    public function testModelShouldFireMoreThanOneActionIfAvailable() {
        $this->model->fireAction('double');
        $this->assertTrue($this->model->MyBehavior->hookedSecond);
        $this->assertTrue($this->model->MyBehavior->hookedFirst);
    }

    /**
     * @testdox fireAction should not fire missing actions
     */
    public function testFireActionShouldNotFireMissingActions() {
        // @todo how could we assert this?
        $this->model->fireAction('missing');
    }

    /**
     * @testdox fireAction should throw exception when firing missing methods
     * @expectedException MissingBehaviorMethodException
     */
    public function testFireActionShouldThrowExceptionWhenFiringMissingMethods() {
        $this->model->fireAction('exception');
    }

    /**
     * @testdox fireAction should accept parameters
     */
    public function testFireActionShouldAcceptParameter() {
        $this->model->fireAction('parameter', array(true));
        $this->assertTrue($this->model->MyBehavior->param);
    }
    
    /**
     * @testdox fireFilter should propagate parameters
     */
    public function testFireFilterShouldPropagateParameters() {
        $actual = $this->model->fireFilter('propagate', true);
        $this->assertTrue($actual);
    }

    /**
     * @testdox fireFilter should stop if filter returns false
     */
    public function testFireFilterShouldStopIfFilterReturnsFalse() {
        $actual = $this->model->fireFilter('stop', true);
        $this->assertFalse($actual);
    }
}