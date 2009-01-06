<?php
/**
 *  Suite de testes da classe Dispatcher.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Tests.Dispatcher
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

include_once "setup.php";

class TestController extends AppController {
    public function index() {
        
    }
}

class TestDispatcher extends UnitTestCase {
    public function setUp() {
        $this->dispatcher = new Dispatcher(false);
        Mapper::prefix("admin");
    }
    public function tearDown() {
        $this->dispatcher = null;
        Mapper::unsetPrefix("admin");
    }
    public function testParseEmptyUrl() {
        $results = $this->dispatcher->parseUrl("");
        $expected = array(
            "here" => "/",
            "prefix" => "",
            "controller" => "home",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseEmptyUrlWithSlash() {
        $results = $this->dispatcher->parseUrl("/");
        $expected = array(
            "here" => "/",
            "prefix" => "",
            "controller" => "home",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseController() {
        $results = $this->dispatcher->parseUrl("/controller");
        $expected = array(
            "here" => "/controller",
            "prefix" => "",
            "controller" => "controller",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerAction() {
        $results = $this->dispatcher->parseUrl("/controller/action");
        $expected = array(
            "here" => "/controller/action",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerActionId() {
        $results = $this->dispatcher->parseUrl("/controller/action/1");
        $expected = array(
            "here" => "/controller/action/1",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerId() {
        $results = $this->dispatcher->parseUrl("/controller/1");
        $expected = array(
            "here" => "/controller/1",
            "prefix" => "",
            "controller" => "controller",
            "action" => "index",
            "id" => "1",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerActionIdParams() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/params");
        $expected = array(
            "here" => "/controller/action/1/params",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array("params"),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerExtension() {
        $results = $this->dispatcher->parseUrl("/controller.html");
        $expected = array(
            "here" => "/controller.html",
            "prefix" => "",
            "controller" => "controller",
            "action" => "index",
            "id" => "",
            "extension" => "html",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerActionExtension() {
        $results = $this->dispatcher->parseUrl("/controller/action.html");
        $expected = array(
            "here" => "/controller/action.html",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "",
            "extension" => "html",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerActionIdExtension() {
        $results = $this->dispatcher->parseUrl("/controller/action/1.html");
        $expected = array(
            "here" => "/controller/action/1.html",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "html",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerActionIdExtensionParams() {
        $results = $this->dispatcher->parseUrl("/controller/action/1.html/params");
        $expected = array(
            "here" => "/controller/action/1.html/params",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "html",
            "params" => array("params"),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerActionIdExtensionManyParams() {
        $results = $this->dispatcher->parseUrl("/controller/action/1.html/params/anotherParam");
        $expected = array(
            "here" => "/controller/action/1.html/params/anotherParam",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "html",
            "params" => array("params", "anotherParam"),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParsePrefixController() {
        $results = $this->dispatcher->parseUrl("/admin/controller");
        $expected = array(
            "here" => "/admin/controller",
            "prefix" => "admin",
            "controller" => "controller",
            "action" => "admin_index",
            "id" => "",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParsePrefix() {
        $results = $this->dispatcher->parseUrl("/admin");
        $expected = array(
            "here" => "/admin",
            "prefix" => "admin",
            "controller" => "home",
            "action" => "admin_index",
            "id" => "",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseControllerWithEndingSlash() {
        $results = $this->dispatcher->parseUrl("/controller/");
        $expected = array(
            "here" => "/controller",
            "prefix" => "",
            "controller" => "controller",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParsePrefixWithEndingSlash() {
        $results = $this->dispatcher->parseUrl("/admin/");
        $expected = array(
            "here" => "/admin",
            "prefix" => "admin",
            "controller" => "home",
            "action" => "admin_index",
            "id" => "",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseWithNamedParam() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/param:value");
        $expected = array(
            "here" => "/controller/action/1/param:value",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array("param" => "value")
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseWithManyNamedParams() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/param:value/anotherParam:anotherValue");
        $expected = array(
            "here" => "/controller/action/1/param:value/anotherParam:anotherValue",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array(),
            "namedParams" => array("param" => "value", "anotherParam" => "anotherValue")
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseWithNumberedAndNamedParam() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/param/param:value");
        $expected = array(
            "here" => "/controller/action/1/param/param:value",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array("param"),
            "namedParams" => array("param" => "value")
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseWithNamedAndNumberedParam() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/param:value/param");
        $expected = array(
            "here" => "/controller/action/1/param:value/param",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array("param"),
            "namedParams" => array("param" => "value")
        );
        $this->assertEqual($expected, $results);
    }
    //public function testParseUrlWithQueryString() {
    //    $results = $this->dispatcher->parseUrl("/?param=value");
    //    $expected = array(
    //        "here" => "/",
    //        "prefix" => "",
    //        "controller" => "home",
    //        "action" => "index",
    //        "id" => "",
    //        "extension" => "htm",
    //        "params" => array(),
    //        "namedParams" => array("param" => "value")
    //    );
    //    $this->assertEqual($expected, $results);
    //}
}

?>