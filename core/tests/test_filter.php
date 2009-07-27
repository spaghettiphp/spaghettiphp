<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Tests.Filter
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

include_once "setup.php";

class BaseFilter extends Filter {
}

class TestFilter extends UnitTestCase {
    public function setUp() {
        $this->filter = new BaseFilter;
    }
    public function tearDown() {
        $this->filter = null;
    }
    public function testParseFilename() {
        $results = $this->filter->parseFilename("file.ext");
        $expected = array("full" => "file.ext", "filename" => "file", "extension" => "ext");
        $this->assertEqual($expected, $results);

        $results = $this->filter->parseFilename("folder/file.ext");
        $expected = array("full" => "folder/file.ext", "filename" => "folder/file", "extension" => "ext");
        $this->assertEqual($expected, $results);

        $results = $this->filter->parseFilename("folder/file");
        $expected = array("full" => "folder/file", "filename" => "folder/file", "extension" => "");
        $this->assertEqual($expected, $results);
        
        $results = $this->filter->parseFilename(".htaccess");
        $expected = array("full" => ".htaccess", "filename" => "", "extension" => "htaccess");
        $this->assertEqual($expected, $results);
    }
}

?>