<?php
/**
 *  Test Case de Mapper
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class TestMapper extends UnitTestCase {
    public function tearDown() {
        $mapper =& Mapper::getInstance();
        $mapper->prefixes = array();
        $mapper->routes = array();
    }
    public function testNormalizeEmptyString() {
        $results = Mapper::normalize("");
        $expected = "/";
        $this->assertEqual($expected, $results);
    }
    public function testNormalizeTrailingSlash() {
        $results = Mapper::normalize("controller/action/");
        $expected = "/controller/action";
        $this->assertEqual($expected, $results);
    }
    public function testNormalizeTrailingSlashes() {
        $results = Mapper::normalize("controller/action//");
        $expected = "/controller/action";
        $this->assertEqual($expected, $results);
    }
    public function testNormalizeNoSlashes() {
        $results = Mapper::normalize("controller/action");
        $expected = "/controller/action";
        $this->assertEqual($expected, $results);
    }
    public function testNormalizeDoubleSlash() {
        $results = Mapper::normalize("/controller//action");
        $expected = "/controller/action";
        $this->assertEqual($expected, $results);
    }
    public function testNormalizeTripleSlash() {
        $results = Mapper::normalize("/controller///action");
        $expected = "/controller/action";
        $this->assertEqual($expected, $results);
    }
    public function testNormalizeProtocol() {
        $results = Mapper::normalize("http://spaghettiphp.org");
        $expected = "http://spaghettiphp.org";
        $this->assertEqual($expected, $results);
    }
    public function testNormalizeMailto() {
        $results = Mapper::normalize("mailto:spaghetti@spaghettiphp.org");
        $expected = "mailto:spaghetti@spaghettiphp.org";
        $this->assertEqual($expected, $results);
    }
    public function testUrlWithSlash() {
        $results = Mapper::url("/controller/action");
        $expected = "/\/controller\/action$/";
        $this->assertPattern($expected, $results);
    }
    public function testUrlWithoutSlash() {
        $results = Mapper::url("controller/action");
        $expected = "/\/controller\/action$/";
        $this->assertPattern($expected, $results);
    }
    public function testUrlWithFullReturn() {
        $results = Mapper::url("controller/action", true);
        $expected = "/^http:\/\/(.*)\/controller\/action$/";
        $this->assertPattern($expected, $results);
    }
}

?>