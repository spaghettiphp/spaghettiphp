<?php
/**
 *  Test Case de MysqlDatasource
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

App::import("Datasource", "mysql_datasource");

class TestMysqlDatasource extends UnitTestCase {
    public function setUp() {
        $this->datasource = new MysqlDatasource();
    }
    public function tearDown() {
        $this->datasource = null;
    }
    public function testSqlConditionsWithPlainCondition() {
        $passed = $this->datasource->sqlConditions(null, "id = 1 OR id = 2");
        $expected = "id = 1 OR id = 2";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithEmptyArray() {
        $passed = $this->datasource->sqlConditions(null, array());
        $expected = "";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithSingleNumericArray() {
        $passed = $this->datasource->sqlConditions(null, array("id = 1"));
        $expected = "id = 1";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithMultipleNumericArray() {
        $passed = $this->datasource->sqlConditions(null, array("id = 1", "id = 2"));
        $expected = "id = 1 AND id = 2";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithSingleAssociativeArray() {
        $passed = $this->datasource->sqlConditions(null, array("id" => "1"));
        $expected = "id = 1";
        $this->assertEqual($passed, $expected);
    }
}

?>