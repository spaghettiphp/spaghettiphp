<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/bootstrap.php';

class ConfigTest extends PHPUnit_Framework_TestCase {
    /**
     * @testdox should read and write config values
     */
    public function testShouldReadAndWriteConfigValues() {
        Config::write('Test.status', $expected = 'success');
        $actual = Config::read('Test.status');
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @testdox read should return null when config key doesn't exist
     */
    public function testReadShouldReturnNullWhenConfigKeyDoesntExist() {
        $this->assertNull(Config::read('Test.notSet'));
    }
}