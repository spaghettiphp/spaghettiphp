<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Tests.Helper.Form
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

include_once "setup.php";
Spaghetti::import("Helper", "html_helper");
Spaghetti::import("Helper", "form_helper");

class TestFormHelper extends UnitTestCase {
    public function setUp() {
        $this->Form = new FormHelper;
    }
    public function tearDown() {
        $this->Form = null;
    }
    public function testCreate() {
        $results = $this->Form->create();
        $expected = "/<form>/";
        $this->assertWantedPattern($expected, $results);
    }
}

?>