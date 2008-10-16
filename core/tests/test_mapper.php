<?php
/**
 *  Put description here.
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
    public function testGetRoute() {
        Mapper::connect("/", "/home");
        $results = Mapper::get_route("/");
        $expected = "/home";
        $this->assertEqual($expected, $results);
        
        Mapper::connect("/", "/home/");
        $results = Mapper::get_route("/");
        $expected = "/home";
        $this->assertEqual($expected, $results);
        
        Mapper::connect("/home/", "/home/index/");
        $results = Mapper::get_route("/");
        $expected = "/home/index";
        $this->assertEqual($expected, $results);

        Mapper::connect("/anything/:any", "/get_anything/$1");
        $results = Mapper::get_route("/anything/index");
        $expected = "/get_anything/index";
        $this->assertEqual($expected, $results);

        Mapper::connect("/numeric/:num", "/get_numeric/$1");
        $results = Mapper::get_route("/numeric/1");
        $expected = "/get_numeric/1";
        $this->assertEqual($expected, $results);

        Mapper::connect("/part/:part/:num/:any", "/test/$1/$2/$3");
        $results = Mapper::get_route("/part/fragment/1/");
        $expected = "/test/fragment/1";
        $this->assertEqual($expected, $results);

        Mapper::connect("/regex/([a-z]*)/([^\d]*)/", "/get_regex/$2/$1");
        $results = Mapper::get_route("/regex/fragment/not_number");
        $expected = "/get_regex/not_number/fragment";
        $this->assertEqual($expected, $results);

        $results = Mapper::get_route("/regex/fragment/1");
        $expected = "/regex/fragment/1";
        $this->assertEqual($expected, $results);
    }
}

?>