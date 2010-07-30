<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/bootstrap.php';
require_once 'lib/behaviors/Hashable.php';

class MyDummyModel extends AppModel {
    public $table = false;
}

class HashableTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->behavior = new Hashable(new MyModel());
    }
    public function tearDown() {
        $this->behavior = null;
    }
    
    /**
     * @testdox hash should leave password alone if it isn't provided
     */
    public function testHashShouldLeavePasswordAloneIfItIsntProvided() {
        $data = $this->behavior->hash(array());
        $this->assertFalse(array_key_exists('password', $data));
    }
    /**
     * @testdox hash should remove password if it is blank
     */
    public function testHashShouldRemovePasswordIfItIsBlank() {
        $data = $this->behavior->hash(array(
            'password' => ''
        ));
        $this->assertFalse(array_key_exists('password', $data));
    }

    /**
     * @testdox hash should hash the password
     */
    public function testHashShouldHashThePassword() {
        $data = $this->behavior->hash(array(
            'password' => '123456'
        ));
        $this->assertEquals(Security::hash('123456'), $data['password']);
    }
}