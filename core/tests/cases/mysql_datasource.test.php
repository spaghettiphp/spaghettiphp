<?php
/**
 *  Test Case de MysqlDatasource
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

App::import("Datasource", "mysql_datasource");

class MysqlDatasourceTest extends MysqlDatasource {
    protected $schema = array(
        "table" => array(
            "id" => array("type" => "integer"),
            "text" => array("type" => "text"),
            "active" => array("type" => "boolean")
        )
    );
}

class TestMysqlDatasource extends UnitTestCase {
    public function setUp() {
        $this->datasource = new MysqlDatasourceTest(array("host" => "localhost", "user" => "root", "password" => "", "database" => "tests"));
        $this->datasource->connect();
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
        $passed = $this->datasource->sqlConditions(null, array("id = 1", "user_id = 2"));
        $expected = "id = 1 AND user_id = 2";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithSingleAssociativeArray() {
        $passed = $this->datasource->sqlConditions(null, array("id" => 1));
        $expected = "id = '1'";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithMultipleAssociativeArray() {
        $passed = $this->datasource->sqlConditions(null, array("id" => 1, "user_id" => 2));
        $expected = "id = '1' AND user_id = '2'";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithComparisonOperator() {
        $passed = $this->datasource->sqlConditions(null, array("id >" => 1));
        $expected = "id > '1'";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithNestedNumericArray() {
        $passed = $this->datasource->sqlConditions(null, array(array("id" => 1, "user_id" => 2)));
        $expected = "(id = '1' AND user_id = '2')";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithNestedNumericArrayAndPlainArray() {
        $passed = $this->datasource->sqlConditions(null, array("id" => "1", array("id" => 1, "user_id" => 2)));
        $expected = "id = '1' AND (id = '1' AND user_id = '2')";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithLogicOperator() {
        $passed = $this->datasource->sqlConditions(null, array("or" => array("id" => 1, "user_id" => 2)));
        $expected = "(id = '1' OR user_id = '2')";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithNestedAssociativeArray() {
        $passed = $this->datasource->sqlConditions(null, array("id" => array("1", "2")));
        $expected = "id IN ('1','2')";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithBetween() {
        $passed = $this->datasource->sqlConditions(null, array("id BETWEEN" => array("1", "2")));
        $expected = "id BETWEEN '1' AND '2'";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithTableSchema() {
        $passed = $this->datasource->sqlConditions("table", array(
            "id" => 1,
            "text" => "Text",
            "active" => false
        ));
        $expected = "id = 1 AND text = 'Text' AND active = 0";
        $this->assertEqual($passed, $expected);
    }
    public function testSqlConditionsWithNullValue() {
        $passed = $this->datasource->sqlConditions("table", array(
            "id" => null,
            "text" => null,
        ));
        $expected = "id = NULL AND text = NULL";
        $this->assertEqual($passed, $expected);
    }
}

?>