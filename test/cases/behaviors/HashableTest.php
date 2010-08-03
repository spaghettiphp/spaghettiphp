<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/test.php';
require_once 'lib/behaviors/Hashable.php';
require_once 'test/classes/models/Users.php';

class HashableTest extends PHPUnit_Framework_TestCase {
    public static function setUpBeforeClass() {
        $connection = Connection::get('test');
        $connection->query(Filesystem::read('test/sql/users_up.sql'));
    }
    public static function tearDownAfterClass() {
        $connection = Connection::get('test');
        $connection->query(Filesystem::read('test/sql/users_down.sql'));
    }
    public function setUp() {
        $this->model = new Users();
        $this->behavior = new Hashable($this->model);
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

    /**
     * @testdox model should save hashed password
     */
    public function testModelShouldSaveHashedPassword() {
        $this->model->save(array(
            'username' => 'spaghettiphp',
            'password' => '123456'
        ));
        $record = $this->model->firstById($this->model->id);
        $this->assertEquals(Security::hash('123456'), $record['password']);
    }
}
