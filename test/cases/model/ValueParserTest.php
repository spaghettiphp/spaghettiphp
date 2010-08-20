<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/test.php';
require_once 'lib/core/model/ValueParser.php';

class ValueParserTest extends PHPUnit_Framework_TestCase {
    /**
     * @testdox conditions should return conditions as SQL
     */
    public function testConditionsShouldReturnConditionsAsSql() {
        $query = new ValueParser(array(
            'a' => 0
        ));
        $expected = 'a = ?';
        $actual = $query->conditions();
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @testdox values should return conditions' values
     */
    public function testValuesShouldReturnConditionsValues() {
        $query = new ValueParser(array(
            'a' => 0
        ));
        $expected = array(0);
        $actual = $query->values();
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * @testdox conditions should implode two or more conditions with AND
     */
    public function testConditionsShouldImplodeTwoConditionsWithAnd() {
        $query = new ValueParser(array(
            'a' => 0,
            'b' => 0,
            'c' => 0
        ));
        $expected = 'a = ? AND b = ? AND c = ?';
        $actual = $query->conditions();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox conditions should parse logical array keys
     */
    public function testConditionsShouldParseLogicalArrayKeys() {
        $query = new ValueParser(array(
            'or' => array(
                'a' => 0,
                'b' => 0
            ),
            'c' => 0
        ));
        $expected = '(a = ? OR b = ?) AND c = ?';
        $actual = $query->conditions();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox conditions should parse nested logical array keys
     */
    public function testConditionsShouldParseNestedLogicalArrayKeys() {
        $query = new ValueParser(array(
            'or' => array(
                'and' => array(
                    'a' => 0,
                    'b' => 0
                ),
                'c' => 0
            )
        ));
        $expected = '((a = ? AND b = ?) OR c = ?)';
        $actual = $query->conditions();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox conditions should parse operators
     */
    public function testConditionsShouldParseOperators() {
        $query = new ValueParser(array(
            'a >' => 0,
            'b <' => 0
        ));
        $expected = 'a > ? AND b < ?';
        $actual = $query->conditions();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox conditions should treat array values as IN()
     */
    public function testConditionsShouldParseIn() {
        $query = new ValueParser(array(
            'a' => array(0, 0)
        ));
        $expected = 'a IN(?,?)';
        $actual = $query->conditions();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox conditions should not parse keys with question marks
     */
    public function testConditionsShouldNotParseKeysWithQuestionMarks() {
        $query = new ValueParser(array(
            'a BETWEEN ? AND ?' => array(0, 1)
        ));
        $expected = 'a BETWEEN ? AND ?';
        $actual = $query->conditions();
        $this->assertEquals($expected, $actual);
    }
}
