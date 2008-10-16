<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Tests.Controller
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

include_once "setup.php";

class BaseController extends AppController {
    public $params = array(
        "controller" => "tests",
        "action" => "render",
        "extension" => "htm"
    );
    public $uses = array();
}

class AutoLayoutController extends BaseController {
}

class ManualLayoutController extends BaseController {
    public $auto_layout = false;
}

class TestController extends UnitTestCase {
    public function setUp() {
        $this->manualLayoutController =& new ManualLayoutController;
        $this->autoLayoutController =& new AutoLayoutController;
    }
    public function tearDown() {
        $this->manualLayoutController = null;
        $this->autoLayoutController = null;
    }
    public function testRender() {
        $results = $this->manualLayoutController->render();
        $expected = null;
        $this->assertEqual("Passed Data: ", $results);

        $this->manualLayoutController->clear();
        $this->manualLayoutController->set("data", "true");
        $results = $this->manualLayoutController->render();
        $expected = null;
        $this->assertEqual("Passed Data: true", $results);

        $results = $this->autoLayoutController->render();
        $expected = null;
        $this->assertEqual('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>Spaghetti</title>
</head>
<body>
Passed Data:  
</body>
</html>', $results);
    }
}

?>