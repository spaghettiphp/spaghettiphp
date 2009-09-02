<?php
/**
 *  Test Case de Dispatcher
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class DispatcherTest extends Dispatcher {
    public function error() {
        return false;
    }
}

class DummyController extends AppController {
    public $autoRender = false;
    public $uses = array();
    public function index() {
    }
    public function id($firstParam = null, $secondParam = null) {
        $this->output = $firstParam;
        return $firstParam;
    }
    private function privateMethod() {
    }
}

class TestDispatcher extends UnitTestCase {
    public function setUp() {
        $this->dispatcher = new DispatcherTest(false);
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array()
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
            "named" => array("param" => "value")
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseWithManynamed() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/param:value/anotherParam:anotherValue");
        $expected = array(
            "here" => "/controller/action/1/param:value/anotherParam:anotherValue",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array(),
            "named" => array("param" => "value", "anotherParam" => "anotherValue")
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
            "named" => array("param" => "value")
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
            "named" => array("param" => "value")
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseWithSpacedParams() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/param+with+space");
        $expected = array(
            "here" => "/controller/action/1/param+with+space",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array("param with space"),
            "named" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testParseWithSpacednamed() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/name:param+with+space");
        $expected = array(
            "here" => "/controller/action/1/name:param+with+space",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "htm",
            "params" => array(),
            "named" => array("name" => "param with space")
        );
        $this->assertEqual($expected, $results);
    }
    //public function testParseUrlWithQueryString() {
    //    $results = $this->dispatcher->parseUrl("/?param=value");
    //    $expected = array(
    //        "here" => "/?param=value",
    //        "prefix" => "",
    //        "controller" => "home",
    //        "action" => "index",
    //        "id" => "",
    //        "extension" => "htm",
    //        "params" => array(),
    //        "named" => array("param" => "value")
    //    );
    //    $this->assertEqual($expected, $results);
    //}
    public function parseUrlWithExtensionAfterParams() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/param.xml");
        $expected = array(
            "here" => "/controller/action/1/param.htm",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "xml",
            "params" => array("param"),
            "named" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function parseUrlWithExtensionAfternamed() {
        $results = $this->dispatcher->parseUrl("/controller/action/1/name:param.xml");
        $expected = array(
            "here" => "/controller/action/1/param.htm",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "xml",
            "params" => array(),
            "named" => array("name" => "param")
        );
        $this->assertEqual($expected, $results);
    }
    
    public function testDispatchWithExistingController() {
        $this->dispatcher->parseUrl("/dummy");
        $results = is_a($this->dispatcher->dispatch(), "DummyController");
        $this->assertTrue($results);
    }
    public function testDispatchWithMissingController() {
        $this->dispatcher->parseUrl("/missing");
        $results = $this->dispatcher->dispatch();
        $this->assertFalse($results);
    }
    public function testDispatchWithMissingAction() {
        $this->dispatcher->parseUrl("/dummy/missing");
        $results = $this->dispatcher->dispatch();
        $this->assertFalse($results);
    }
    public function testDispatchWithPrivateAction() {
        $this->dispatcher->parseUrl("/dummy/privateMethod");
        $results = $this->dispatcher->dispatch();
        $this->assertFalse($results);
    }
    public function testDispatchWithId() {
        $this->dispatcher->parseUrl("/dummy/id/1");
        $controller = $this->dispatcher->dispatch();
        $results = $controller->output;
        $expected = 1;
        $this->assertEqual($expected, $results);
    }
    public function testDispatchWithoutId() {
        $this->dispatcher->parseUrl("/dummy/id");
        $controller = $this->dispatcher->dispatch();
        $results = $controller->output;
        $expected = null;
        $this->assertIdentical($expected, $results);
    }
    public function testDispatchWithIdAndParams() {
        $this->dispatcher->parseUrl("/dummy/id/1/param");
        $controller = $this->dispatcher->dispatch();
        $results = $controller->output;
        $expected = 1;
        $this->assertEqual($expected, $results);
    }
    public function testDispatchWithParamsAndWithoutId() {
        $this->dispatcher->parseUrl("/dummy/id/param");
        $controller = $this->dispatcher->dispatch();
        $results = $controller->output;
        $expected = "param";
        $this->assertEqual($expected, $results);
    }
}

?>