<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/bootstrap.php';

class StringTest extends PHPUnit_Framework_TestCase {
    /**
     * @testdox insert should replace :vars with actual content
     */
    public function testInsertShouldReplaceVarsWithActualContent() {
        $expected = 'Spaghetti Framework';
        $actual = String::insert(':name :type', array(
            'name' => 'Spaghetti',
            'type' => 'Framework'
        ));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox insert should replace underscored :vars with actual content
     */
    public function testInsertShouldReplaceUnderscoredVarsWithActualContent() {
        $expected = 'Spaghetti Framework';
        $actual = String::insert(':name_and_type', array(
            'name_and_type' => 'Spaghetti Framework'
        ));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox insert should not replace eagerly
     */
    public function testInsertShouldNotReplaceEagerly() {
        $expected = 'Not Eager';
        $actual = String::insert(':not :not_eager', array(
            'not' => 'Not',
            'not_eager' => 'Eager'
        ));
        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox extract should extract :vars from a string
     */
    public function testExtractShouldExtractVarsFromAString() {
        $expected = array('extract', 'vars');
        $actual = String::extract(':extract :vars');
        $this->assertEquals($expected, $actual);
    }
}