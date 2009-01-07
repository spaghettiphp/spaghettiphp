<?php
/**
 *  Suite de testes da classe Mapper.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Tests.Mapper
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

include_once "setup.php";

class TestMapper extends UnitTestCase {
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
}

?>