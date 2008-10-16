<?php
/**
 *  Put description here.
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

class BaseController extends AppController {
    public $uses = array();
    public function render() {
        $this->output = "";
        if($this->view_data["id"]) $this->output .= "id: {$this->view_data['id']}\n";
        if($this->view_data["param"]) $this->output .= "param: {$this->view_data['param']}\n";
        $this->output .= "This should work!";
        return $this->output;
    }    
}

class AutoRenderController extends BaseController {
    public function test_action($id = null, $first_param = null) {
        $this->set("id", $id);
        $this->set("param", $first_param);
    }
}

class ManualRenderController extends BaseController {
    public function test_action($id = null, $first_param = null) {
        $this->set("id", $id);
        $this->set("param", $first_param);
        $this->render();
    }
}

class BaseFilter extends Filter {
    public function start($file) {
        echo $file;
    }
}

class TestDispatcher extends UnitTestCase {
    public function setUp() {
        $this->dispatcher =& new Dispatcher(false);
        Mapper::prefix("admin");
    }
    public function tearDown() {
        $this->dispatcher = null;
    }
    public function testParseUrl() {
        $results = $this->dispatcher->parse_url("");
        $expected = array(
            "here" => "/",
            "prefix" => "",
            "controller" => "home",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        $results = $this->dispatcher->parse_url("controller");
        $expected = array(
            "here" => "/controller",
            "prefix" => "",
            "controller" => "controller",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        $results = $this->dispatcher->parse_url("controller/action/id");
        $expected = array(
            "here" => "/controller/action/id",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "",
            "extension" => "htm",
            "params" => array("id")
        );
        $this->assertEqual($expected, $results);

        $results = $this->dispatcher->parse_url("controller/action/1.xml/params/");
        $expected = array(
            "here" => "/controller/action/1.xml/params",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "1",
            "extension" => "xml",
            "params" => array("params")
        );
        $this->assertEqual($expected, $results);

        $results = $this->dispatcher->parse_url("controller/action.xml");
        $expected = array(
            "here" => "/controller/action.xml",
            "prefix" => "",
            "controller" => "controller",
            "action" => "action",
            "id" => "",
            "extension" => "xml",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        $results = $this->dispatcher->parse_url("controller.xml");
        $expected = array(
            "here" => "/controller.xml",
            "prefix" => "",
            "controller" => "controller",
            "action" => "index",
            "id" => "",
            "extension" => "xml",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        $results = $this->dispatcher->parse_url("admin/controller/action");
        $expected = array(
            "here" => "/admin/controller/action",
            "prefix" => "admin",
            "controller" => "controller",
            "action" => "admin_action",
            "id" => "",
            "extension" => "htm",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        $results = $this->dispatcher->parse_url("admin");
        $expected = array(
            "here" => "/admin",
            "prefix" => "admin",
            "controller" => "",
            "action" => "admin_index",
            "id" => "",
            "extension" => "htm",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        $results = $this->dispatcher->parse_url("administrator");
        $expected = array(
            "here" => "/administrator",
            "prefix" => "",
            "controller" => "administrator",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        Mapper::connect("/admin", "/admin/home");
        $results = $this->dispatcher->parse_url("admin");
        $expected = array(
            "here" => "/admin",
            "prefix" => "admin",
            "controller" => "home",
            "action" => "admin_index",
            "id" => "",
            "extension" => "htm",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        Mapper::connect("/dummy_route/", "/controller/");
        $results = $this->dispatcher->parse_url("/dummy_route");
        $expected = array(
            "here" => "/dummy_route",
            "prefix" => "",
            "controller" => "controller",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array()
        );
        $this->assertEqual($expected, $results);

        Mapper::connect("/dummy_route/:any", "/controller/$1");
        $results = $this->dispatcher->parse_url("dummy_route");
        $expected = array(
            "here" => "/dummy_route",
            "prefix" => "",
            "controller" => "controller",
            "action" => "index",
            "id" => "",
            "extension" => "htm",
            "params" => array()
        );
        $this->assertEqual($expected, $results);
    }
    public function testDispatch() {
        ob_start();
        $this->dispatcher->parse_url("auto_render/test_action/");
        $this->dispatcher->dispatch();
        $results = ob_get_clean();
        $expected = "This should work!";
        $this->assertEqual($expected, $results);

        ob_start();
        $this->dispatcher->parse_url("auto_render/test_action/1/dummy_param");
        $this->dispatcher->dispatch();
        $results = ob_get_clean();
        $expected = "id: 1\nparam: dummy_param\nThis should work!";
        $this->assertEqual($expected, $results);

        ob_start();
        $this->dispatcher->parse_url("manual_render/test_action/");
        $this->dispatcher->dispatch();
        $results = ob_get_clean();
        $expected = "This should work!";
        $this->assertEqual($expected, $results);

        ob_start();
        Config::write("filters", array("base"));
        $this->dispatcher->parse_url("base/file.ext");
        $this->dispatcher->dispatch();
        $results = ob_get_clean();
        $expected = "file.ext";
        $this->assertEqual($expected, $results);
    }
}

?>