<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Tests.Helper.Html
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

include_once "setup.php";
Spaghetti::import("Helper", "html_helper");

class TestHtmlHelper extends UnitTestCase {
    public function setUp() {
        $this->Html = new HtmlHelper;
    }
    public function tearDown() {
        $this->Html = null;
    }
    public function testTag() {
        $results = $this->Html->tag("p", "Test");
        $expected = "<p>Test</p>";
        $this->assertEqual($expected, $results);
        
        $results = $this->Html->tag("br", null, null, false);
        $expected = "<br />";
        $this->assertEqual($expected, $results);
        
        $results = $this->Html->tag("a", "link", array("href" => "http://spaghettiphp.org/"));
        $expected = "<a href=\"http://spaghettiphp.org/\">link</a>";
        $this->assertEqual($expected, $results);

        $results = $this->Html->tag("input", null, array("type" => "text"), false);
        $expected = "<input type=\"text\" />";
        $this->assertEqual($expected, $results);

        $results = $this->Html->tag("input", null, array("type" => "checkbox", "checked" => true), false);
        $expected = "<input type=\"checkbox\" checked=\"checked\" />";
        $this->assertEqual($expected, $results);
        
        $results = $this->Html->tag("input", "NO!", array("type" => "text"), false);
        $expected = "<input type=\"text\" />";
        $this->assertEqual($expected, $results);
    }
    public function testLink() {
        $results = $this->Html->link("link", "/home/index");
        $expected = "/<a href=\"\/.*home\/index\">link<\/a>/";
        $this->assertWantedPattern($expected, $results);

        $results = $this->Html->link("link", "/home/index", null, true);
        $expected = "/<a href=\"http:\/\/.*home\/index\">link<\/a>/";
        $this->assertWantedPattern($expected, $results);

        $results = $this->Html->link("link", "/home/index", array("target" => "_blank"));
        $expected = "/<a href=\"\/.*home\/index\" target=\"_blank\">link<\/a>/";
        $this->assertWantedPattern($expected, $results);

        $results = $this->Html->link($this->Html->image("image.jpg", "image"), "/home/index");
        $expected = "/<a href=\"\/.*home\/index\"><img src=\"\/.*images\/image\.jpg\" alt=\"image\" \/><\/a>/";
        $this->assertWantedPattern($expected, $results);
    }
    public function testImage() {
        $results = $this->Html->image("image.jpg", "image");
        $expected = "/<img src=\"\/.*images\/image\.jpg\" alt=\"image\" \/>/";
        $this->assertWantedPattern($expected, $results);

        $results = $this->Html->image("image.jpg", "image", array("class" => "image"));
        $expected = "/<img src=\"\/.*images\/image\.jpg\" alt=\"image\" class=\"image\" \/>/";
        $this->assertWantedPattern($expected, $results);
    }
    public function testStylesheet() {
        $results = $this->Html->stylesheet("styles.css");
        $expected = "/<link href=\"\/.*styles\/styles\.css\" rel=\"stylesheet\" type=\"text\/css\" \/>/";
        $this->assertWantedPattern($expected, $results);
    }
    public function testScript() {
        $results = $this->Html->script("script.js");
        $expected = "/<script src=\"\/.*scripts\/script\.js\" type=\"text\/javascript\"><\/script>/";
        $this->assertWantedPattern($expected, $results);
    }
}

?>