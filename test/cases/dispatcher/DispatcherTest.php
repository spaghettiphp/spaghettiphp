<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/bootstrap.php';

class ExistingController extends AppController {
    public $uses = array();
    public $layout = false;
    public function index() {}
}

class DispatcherTest extends PHPUnit_Framework_TestCase {
    public static $defaults = array(
        'controller' => 'home',
        'action' => 'index',
        'extension' => 'htm',
        'params' => array(),
        'named' => array(),
        'id' => null
    );

    /**
     * @testdox dispatch should throw MissingControllerException when controller does not exist
     * @expectedException MissingControllerException
     */
    public function testDispatchShouldThrowMissingControllerException() {
        Dispatcher::dispatch(array(
            'controller' => 'missing'
        ) + self::$defaults);
    }
    
    /**
     * @testdox dispatch should throw MissingActionException when action does not exist
     * @expectedException MissingActionException
     */
    public function testDispatchShouldThrowMissingActionException() {
        Dispatcher::dispatch(array(
            'action' => 'missing'
        ) + self::$defaults);
    }
    
    /**
     * @testdox dispatch should render output when both controller and action exist
     */
    public function testDispatchShouldRenderOutputWhenContollerAndActionExist() {
        $output = Dispatcher::dispatch(self::$defaults);
        $this->assertNotEquals('', $output);
    }

    /**
     * @testdox dispatch should render output when only controller and view exist
     */
    public function testDispatchShouldRenderOutputWhenContollerAndViewExist() {
        $expected = 'working';
        // create a test view
        Filesystem::createDir('app/views/existing', 0777);
        Filesystem::write('app/views/existing/test.htm.php', $expected);
        
        $output = Dispatcher::dispatch(array(
            'controller' => 'existing',
            'action' => 'test'
        ) + self::$defaults);
        
        // destroy the test view
        Filesystem::delete('app/views/existing');
        
        $this->assertEquals($expected, $output);
    }

    /**
     * @testdox dispatch should render output when only view exists
     */
    public function testDispatchShouldRenderOutputWhenViewExists() {
        // create a test view
        Filesystem::createDir('app/views/missing', 0777);
        Filesystem::write('app/views/missing/test.htm.php', 'working');
        
        $output = Dispatcher::dispatch(array(
            'controller' => 'missing',
            'action' => 'test'
        ) + self::$defaults);
        
        // destroy the test view
        Filesystem::delete('app/views/missing');
        
        $this->assertNotEquals('', $output);
    }
}