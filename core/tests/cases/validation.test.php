<?php
/**
 *  Test Case de Validation
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class TestValidation extends UnitTestCase {
    public function testValidAlphanumeric() {
        $value = "SpaghettiPHP02";
        $this->assertTrue(Validation::alphanumeric($value));
    }
    public function testInvalidAlphanumeric() {
        $value = "Spaghetti* PHP 0.2";
        $this->assertFalse(Validation::alphanumeric($value));
    }
    public function testValidNumeric() {
        $value = "+42.053e25";
        $this->assertTrue(Validation::numeric($value));
    }
    public function testInvalidNumeric() {
        $value = "+-42.0asd53e25";
        $this->assertFalse(Validation::numeric($value));
    }
    public function testValidDecimalWithPoint() {
        $value = "42.123";
        $this->assertTrue(Validation::decimal($value));
    }
    public function testValidDecimalWithSignal() {
        $value = "+42.0";
        $this->assertTrue(Validation::decimal($value));
    }
    public function testValidDecimalWithScientificFloat() {
        $value = "42.60e-10";
        $this->assertTrue(Validation::decimal($value));
    }
    public function testValidDecimalWithFixedPlaces() {
        $value = "42.60";
        $this->assertTrue(Validation::decimal($value, 2));
    }
    public function testInvalidDecimalWithoutPoint() {
        $value = "42";
        $this->assertFalse(Validation::decimal($value));
    }
    public function testInvalidDecimalWithJustPoint() {
        $value = "42.";
        $this->assertFalse(Validation::decimal($value));
    }
    public function testValidMaxLength() {
        $value = "42424";
        $this->assertTrue(Validation::maxLength($value, 5));
    }
    public function testValidMaxLengthWithLessCharacters() {
        $value = "42";
        $this->assertTrue(Validation::maxLength($value, 5));
    }
    public function testInvalidMaxLength() {
        $value = "424242";
        $this->assertFalse(Validation::maxLength($value, 5));
    }
    public function testValidMinLength() {
        $value = "42";
        $this->assertTrue(Validation::minLength($value, 2));
    }
    public function testValidMinLengthWithMoreCharacters() {
        $value = "4242";
        $this->assertTrue(Validation::minLength($value, 2));
    }
    public function testInvalidMinLength() {
        $value = "4";
        $this->assertFalse(Validation::minLength($value, 2));
    }
    public function testValidBetweenWithString() {
        $value = "Spaghetti";
        $this->assertTrue(Validation::between($value, 3, 10));
    }
    public function testValidBetweenWithNumber() {
        $value = 5;
        $this->assertTrue(Validation::between($value, 3, 10));
    }
    public function testInvalidBetweenWithString() {
        $value = "Spaghetti* Framework";
        $this->assertFalse(Validation::between($value, 3, 10));
    }
    public function testInvalidBetweenWithNumber() {
        $value = 1;
        $this->assertFalse(Validation::between($value, 3, 10));
    }
    public function testValidBooleanWithNumber() {
        $value = 0;
        $this->assertTrue(Validation::boolean($value));
    }
    public function testValidBooleanWithString() {
        $value = '1';
        $this->assertTrue(Validation::boolean($value));
    }
    public function testValidBooleanWithBooleanTrue() {
        $value = true;
        $this->assertTrue(Validation::boolean($value));
    }
    public function testValidBooleanWithBooleanFalse() {
        $value = false;
        $this->assertTrue(Validation::boolean($value));
    }
    public function testValidNotEmpty() {
        $value = "Spaghetti";
        $this->assertTrue(Validation::notEmpty($value));
    }
    public function testValidNotEmptyWithZero() {
        $value = "0";
        $this->assertTrue(Validation::notEmpty($value));
    }
    public function testInvalidNotEmptyWithEmptyString() {
        $value = "";
        $this->assertFalse(Validation::notEmpty($value));
    }
    public function testInvalidNotEmptyWithSpaces() {
        $value = "   ";
        $this->assertFalse(Validation::notEmpty($value));
    }
    public function testValidInList() {
        $value = "Spaghetti";
        $list = array("Spaghetti", "Framework");
        $this->assertTrue(Validation::inList($value, $list));
    }
    public function testInvalidInList() {
        $value = "PHP";
        $list = array("Spaghetti", "Framework");
        $this->assertFalse(Validation::inList($value, $list));
    }
    public function testValidMultipleWithSingleOption() {
        $values = array("Spaghetti" => 1);
        $list = array("Spaghetti", "Framework");
        $this->assertTrue(Validation::multiple($values, $list));
    }
    public function testValidMultipleWithMultipleOptions() {
        $values = array("Spaghetti" => 1, "Framework" => 1);
        $list = array("Spaghetti", "Framework");
        $this->assertTrue(Validation::multiple($values, $list));
    }
    public function testValidMultipleWithMinAndMax() {
        $values = array("Spaghetti" => 1, "Framework" => 1);
        $list = array("Spaghetti", "Framework");
        $this->assertTrue(Validation::multiple($values, $list, 1, 2));
    }
    public function testInvalidMultiple() {
        $values = array("PHP" => 1);
        $list = array("Spaghetti", "Framework");
        $this->assertFalse(Validation::multiple($values, $list));
    }
    public function testInvalidMultipleWithZeroedValue() {
        $values = array("Spaghetti" => 0);
        $list = array("Spaghetti", "Framework");
        $this->assertFalse(Validation::multiple($values, $list));
    }
    public function testInvalidMultipleWithMin() {
        $values = array("Spaghetti" => 1);
        $list = array("Spaghetti", "Framework");
        $this->assertFalse(Validation::multiple($values, $list, 2));
    }
    public function testInvalidMultipleWithMax() {
        $values = array("Spaghetti" => 1, "Framework" => 1);
        $list = array("Spaghetti", "Framework");
        $this->assertFalse(Validation::multiple($values, $list, null, 1));
    }
    public function testValidBlank() {
        $value = "";
        $this->assertTrue(Validation::blank($value));
    }
    public function testValidBlankWithWhitespaces() {
        $value = "    ";
        $this->assertTrue(Validation::blank($value));
    }
    public function testInvalidBlank() {
        $value = "Spaghetti";
        $this->assertFalse(Validation::blank($value));
    }
}

?>