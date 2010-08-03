<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/test.php';

class HomeController extends AppController {
    public $controllerEvents = array();
    public $uses = array();
    public function index() {}
    protected function secret_method() {}
    protected function beforeFilter() {
        $this->controllerEvents []= 'beforeFilter';
    }
    protected function beforeRender() {
        $this->controllerEvents []= 'beforeRender';
    }
    protected function afterFilter() {
        $this->controllerEvents []= 'afterFilter';
    }
}

class MissingModelController extends AppController {
    public $uses = array('Missing');
}

class ControllerTest extends PHPUnit_Framework_TestCase {
    public static $defaults = array(
        'controller' => 'home',
        'action' => 'index',
        'extension' => 'htm',
        'params' => array(),
        'id' => null
    );
    public function setUp() {
        $this->controller = new HomeController();
    }
    public function tearDown() {
        $this->controller = null;
    }
    
    /**
     * @testdox hasAction should return true to public actions from current controller
     */
    public function testHasActionShouldReturnTrueToPublicActionsFromCurrentController() {
        $actual = $this->controller->hasAction('index');
        $this->assertTrue($actual);
    }

    /**
     * @testdox hasAction should return false to private/protected methods from controller
     */
    public function testHasActionShouldReturnFalseToProtectedMethodsFromController() {
        $actual = $this->controller->hasAction('secret_method');
        $this->assertFalse($actual);
    }

    /**
     * @testdox hasAction should return false to public methods from superclass
     */
    public function testHasActionShouldReturnFalseToPublicMethodsFromSuperclass() {
        $actual = $this->controller->hasAction('hasAction');
        $this->assertFalse($actual);
    }
    
    /**
     * @testdox callAction should call all controller events in order
     */
    public function testCallActionShouldCallAllControllerEventsInOrder() {
        $expected = array('beforeFilter', 'beforeRender', 'afterFilter');
        $this->controller->callAction(self::$defaults);
        $actual = $this->controller->controllerEvents;
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox loadModel should throw exception if model doesn't exist
     * @expectedException MissingModelException
     */
    public function testLoadModelShouldThrowExceptionIfModelDoesntExist() {
        $controller = Controller::load('MissingModelController', true);
    }

    /**
     * @testdox set should work with arrays
     */
    public function testSetShouldWorkWithArrays() {
        $this->controller->set(array('key' => 'value'));
        $this->assertEquals('value', $this->controller->get('key'));
    }

    /**
     * @testdox get should return null for undefined keys
     */
    public function testGetShouldReturnNullForUndefinedKeys() {
        $this->assertNull($this->controller->get('undefined'));
    }
}
