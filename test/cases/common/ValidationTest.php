<?php

require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config/test.php';

class ValidationTest extends PHPUnit_Framework_TestCase {
    /**
     * @testdox alphanumeric should return true to letters and numbers
     */
    public function testValidAlphanumeric() {
        $value = 'SpaghettiPHP1234';
        $this->assertTrue(Validation::alphanumeric($value));
    }
    
    /**
     * @testdox alphanumeric should return false to symbols
     */
    public function testInvalidAlphanumeric() {
        $value = 'Spaghetti* PHP 1.2.3.4';
        $this->assertFalse(Validation::alphanumeric($value));
    }
    
    /**
     * @testdox numeric should return true to valid numbers
     */
    public function testValidNumeric() {
        $value = '+42.053e25';
        $this->assertTrue(Validation::numeric($value));
    }
    /**
     * @testdox numeric should return false to invalid numbers
     */
    public function testInvalidNumeric() {
        $value = '+-42.0asd53e25';
        $this->assertFalse(Validation::numeric($value));
    }
    
    /**
     * @testdox decimal should return true to valid decimal numbers
     */
    public function testValidDecimalWithPoint() {
        $value = '42.123';
        $this->assertTrue(Validation::decimal($value));
    }

    /**
     * @testdox decimal should return true to decimal numbers with signal
     */
    public function testValidDecimalWithSignal() {
        $value = '+42.0';
        $this->assertTrue(Validation::decimal($value));
    }

    /**
     * @testdox decimal should return true to decimal numbers in scientific notation
     */
    public function testValidDecimalWithScientificFloat() {
        $value = '42.60e-10';
        $this->assertTrue(Validation::decimal($value));
    }

    /**
     * @testdox decimal should return true to decimal numbers with fixed decimal places
     */
    public function testValidDecimalWithFixedPlaces() {
        $value = '42.60';
        $this->assertTrue(Validation::decimal($value, 2));
    }

    /**
     * @testdox decimal should return false to decimal numbers non-matching fixed decimal places
     */
    public function testInvalidDecimalWithFixedPlaces() {
        $value = '42.60';
        $this->assertFalse(Validation::decimal($value, 3));
    }
    
    /**
     * @testdox decimal should return false to numbers without decimal point
     */
    public function testInvalidDecimalWithoutPoint() {
        $value = '42';
        $this->assertFalse(Validation::decimal($value));
    }

    /**
     * @testdox decimal should return false to numbers with missing decimal places
     */
    public function testInvalidDecimalWithJustPoint() {
        $value = '42.';
        $this->assertFalse(Validation::decimal($value));
    }
    
    /**
     * @testdox maxLength should return true to strings matching specified length
     */
    public function testValidMaxLength() {
        $value = '42424';
        $this->assertTrue(Validation::maxLength($value, 5));
    }
    
    /**
     * @testdox maxLength should return true to strings with less characters than specified
     */
    public function testValidMaxLengthWithLessCharacters() {
        $value = '42';
        $this->assertTrue(Validation::maxLength($value, 5));
    }
    
    /**
     * @testdox maxLength should return false to strings exceeding specified length
     */
    public function testInvalidMaxLength() {
        $value = '424242';
        $this->assertFalse(Validation::maxLength($value, 5));
    }
    
    /**
     * @testdox minLength should return true to strings matching specified length
     */
    public function testValidMinLength() {
        $value = '42';
        $this->assertTrue(Validation::minLength($value, 2));
    }

    /**
     * @testdox maxLength should return true to strings with more characters than specified
     */
    public function testValidMinLengthWithMoreCharacters() {
        $value = '4242';
        $this->assertTrue(Validation::minLength($value, 2));
    }
    
    
    /**
     * @testdox maxLength should return false to strings exceeding specified length
     */
    public function testInvalidMinLength() {
        $value = '4';
        $this->assertFalse(Validation::minLength($value, 2));
    }
    
    /**
     * @testdox between should return true to strings with length between specified boundaries
     */
    public function testValidBetweenWithString() {
        $value = 'Spaghetti';
        $this->assertTrue(Validation::between($value, 3, 10));
    }
    
    /**
     * @testdox between should return true to numbers between specified boundaries
     */
    public function testValidBetweenWithNumber() {
        $value = 5;
        $this->assertTrue(Validation::between($value, 3, 10));
    }
    
    /**
     * @testdox between should return false to strings with length outside specified boundaries
     */
    public function testInvalidBetweenWithString() {
        $value = 'Spaghetti* Framework';
        $this->assertFalse(Validation::between($value, 3, 10));
    }

    /**
     * @testdox between should return false to numbers outside specified boundaries
     */
    public function testInvalidBetweenWithNumber() {
        $value = 1;
        $this->assertFalse(Validation::between($value, 3, 10));
    }
    
    
    /**
     * @testdox boolean should return true to zero as int
     */
    public function testValidBooleanWithNumber() {
        $value = 0;
        $this->assertTrue(Validation::boolean($value));
    }

    /**
     * @testdox boolean should return true to one as string
     */
    public function testValidBooleanWithString() {
        $value = '1';
        $this->assertTrue(Validation::boolean($value));
    }

    /**
     * @testdox boolean should return true to true as boolean
     */
    public function testValidBooleanWithBooleanTrue() {
        $value = true;
        $this->assertTrue(Validation::boolean($value));
    }
    
    /**
     * @testdox boolean should return true to false as boolean
     */
    public function testValidBooleanWithBooleanFalse() {
        $value = false;
        $this->assertTrue(Validation::boolean($value));
    }

    /**
     * @testdox boolean should return false to true as string
     */
    public function testInvalidBooleanWithTrue() {
        $value = 'true';
        $this->assertFalse(Validation::boolean($value));
    }
    
    /**
     * @testdox notEmpty should return true to non-empty string
     */
    public function testValidNotEmpty() {
        $value = 'Spaghetti';
        $this->assertTrue(Validation::notEmpty($value));
    }

    /**
     * @testdox notEmpty should return true to '0' string
     */
    public function testValidNotEmptyWithZero() {
        $value = '0';
        $this->assertTrue(Validation::notEmpty($value));
    }
    
    /**
     * @testdox notEmpty should return false to empty string
     */
    public function testInvalidNotEmptyWithEmptyString() {
        $value = '';
        $this->assertFalse(Validation::notEmpty($value));
    }
    
    /**
     * @testdox notEmpty should return false to blank space string
     */
    public function testInvalidNotEmptyWithSpaces() {
        $value = '   ';
        $this->assertFalse(Validation::notEmpty($value));
    }
    
    /**
     * @testdox inList should return true to element within the list
     */
    public function testValidInList() {
        $value = 'Spaghetti';
        $list = array('Spaghetti', 'Framework');
        $this->assertTrue(Validation::inList($value, $list));
    }
    
    /**
     * @testdox inList should return false to element not within the list
     */
    public function testInvalidInList() {
        $value = 'PHP';
        $list = array('Spaghetti', 'Framework');
        $this->assertFalse(Validation::inList($value, $list));
    }
    
    /**
     * @testdox multiple should return true to single options within specified list
     */
    public function testValidMultipleWithSingleOption() {
        $values = array('Spaghetti' => 1);
        $list = array('Spaghetti', 'Framework');
        $this->assertTrue(Validation::multiple($values, $list));
    }
    
    /**
     * @testdox multiple should return true to multiple options within specified list
     */
    public function testValidMultipleWithMultipleOptions() {
        $values = array('Spaghetti' => 1, 'Framework' => 1);
        $list = array('Spaghetti', 'Framework');
        $this->assertTrue(Validation::multiple($values, $list));
    }
    
    /**
     * @testdox multiple should return true to multiple options within specified list and range
     */
    public function testValidMultipleWithMinAndMax() {
        $values = array('Spaghetti' => 1, 'Framework' => 1);
        $list = array('Spaghetti', 'Framework');
        $this->assertTrue(Validation::multiple($values, $list, 1, 2));
    }
    
    /**
     * @testdox multiple should return false to option not in the list
     */
    public function testInvalidMultiple() {
        $values = array('PHP' => 1);
        $list = array('Spaghetti', 'Framework');
        $this->assertFalse(Validation::multiple($values, $list));
    }
    
    /**
     * @testdox multiple should return false to zeroed option
     */
    public function testInvalidMultipleWithZeroedValue() {
        $values = array('Spaghetti' => 0);
        $list = array('Spaghetti', 'Framework');
        $this->assertFalse(Validation::multiple($values, $list));
    }
    
    /**
     * @testdox multiple should return false to selected options below specified range
     */
    public function testInvalidMultipleWithMin() {
        $values = array('Spaghetti' => 1);
        $list = array('Spaghetti', 'Framework');
        $this->assertFalse(Validation::multiple($values, $list, 2));
    }
    
    /**
     * @testdox multiple should return false to selected options above specified range
     */
    public function testInvalidMultipleWithMax() {
        $values = array('Spaghetti' => 1, 'Framework' => 1);
        $list = array('Spaghetti', 'Framework');
        $this->assertFalse(Validation::multiple($values, $list, null, 1));
    }
    
    /**
     * @testdox blank should return true to blank string
     */
    public function testValidBlank() {
        $value = '';
        $this->assertTrue(Validation::blank($value));
    }

    /**
     * @testdox blank should return true to spaces
     */
    public function testValidBlankWithWhitespaces() {
        $value = '    ';
        $this->assertTrue(Validation::blank($value));
    }

    /**
     * @testdox blank should return false to non-empty string
     */
    public function testInvalidBlank() {
        $value = 'Spaghetti';
        $this->assertFalse(Validation::blank($value));
    }

    /**
     * @testdox valid should return true to smaller > bigger number
     */
    public function testValidComparisonWithSymbol() {
        $value1 = 42;
        $value2 = 3.14;
        $this->assertTrue(Validation::comparison($value1, '>', $value2));
    }

    /**
     * @testdox valid should return true to smaller less bigger number
     */
    public function testValidComparisonWithText() {
        $value1 = 3.14;
        $value2 = 42;
        $this->assertTrue(Validation::comparison($value1, 'less', $value2));
    }

    /**
     * @testdox valid should return false to number equal other number
     */
    public function testInvalidComparison() {
        $value1 = 3.14;
        $value2 = 42;
        $this->assertFalse(Validation::comparison($value1, 'equal', $value2));
    }

    /**
     * @testdox equal should return true to string == string
     */
    public function testValidEqual() {
        $value = 'Spaghetti';
        $this->assertTrue(Validation::equal($value, $value));
    }

    /**
     * @testdox equal should return false to string == other string
     */
    public function testInvalidEqual() {
        $value = 'Spaghetti';
        $compare = 'Framework';
        $this->assertFalse(Validation::equal($value, $compare));
    }
    
    /**
     * @testdox equal should return false to number == 'number'
     */
    public function testInvalidEqualWithIdenticalComparison() {
        $value = 42;
        $compare = '42';
        $this->assertFalse(Validation::equal($value, $compare));
    }
    
    /**
     * @testdox range should return true to finite number
     */
    public function testValidRangeWithFiniteNumerical() {
        $value = 42;
        $this->assertTrue(Validation::range($value));
    }

    /**
     * @testdox range should return true to finite number as string
     */
    public function testValidRangeWithFiniteNumericalAsString() {
        $value = '42';
        $this->assertTrue(Validation::range($value));
    }

    /**
     * @testdox range should return true to number above lower limit
     */
    public function testValidRangeWithLower() {
        $value = 42;
        $lower = 0;
        $this->assertTrue(Validation::range($value, $lower));
    }

    /**
     * @testdox range should return true to number below upper limit
     */
    public function testValidRangeWithUpper() {
        $value = 42;
        $upper = 84;
        $this->assertTrue(Validation::range($value, null, $upper));
    }

    /**
     * @testdox range should return false to number below lower limit
     */
    public function testInvalidRangeWithLower() {
        $value = 0;
        $lower = 42;
        $this->assertFalse(Validation::range($value, $lower));
    }

    /**
     * @testdox range should return false to number above upper limit
     */
    public function testInvalidRangeWithUpper() {
        $value = 42;
        $upper = 0;
        $this->assertFalse(Validation::range($value, null, $upper));
    }

    /**
     * @testdox range should return false to non-numerical value
     */
    public function testInvalidRangeWithNonNumericValue() {
        $value = 'Non numeric';
        $this->assertFalse(Validation::range($value));
    }

    /**
     * @testdox ip should return true to valid IPs
     */
    public function testValidIp() {
        $value = '127.0.0.1';
        $this->assertTrue(Validation::ip($value));
    }

    /**
     * @testdox ip should return false to invalid IPs
     */
    public function testInvalidIp() {
        $value = '123.456.789.0';
        $this->assertFalse(Validation::ip($value));
    }

    /**
     * @testdox ip should return false to malformed IPs
     */
    public function testInvalidIpAsMalformed() {
        $value = '123456789';
        $this->assertFalse(Validation::ip($value));
    }

    /**
     * @testdox url should return true to URLs with IPs
     */
    public function testValidUrlWithIp() {
        $value = 'http://127.0.0.1';
        $this->assertTrue(Validation::url($value));
    }
    
    /**
     * @testdox url should return true to URLs with hostname
     */
    public function testValidUrlWithHostname() {
        $value = 'http://spaghettiphp.org';
        $this->assertTrue(Validation::url($value));
    }

    /**
     * @testdox url should return true to URLs with hostname and port number
     */
    public function testValidUrlWithHostnameAndPort() {
        $value = 'http://spaghettiphp.org:8080';
        $this->assertTrue(Validation::url($value));
    }

    /**
     * @testdox url should return true to URLs with paths
     */
    public function testValidUrlWithPath() {
        $value = 'http://spaghettiphp.org/download/download/';
        $this->assertTrue(Validation::url($value));
    }

    /**
     * @testdox url should return true to URLs with query strings
     */
    public function testValidUrlWithQueryString() {
        $value = 'http://spaghettiphp.org/download?redirect=true';
        $this->assertTrue(Validation::url($value));
    }

    /**
     * @testdox url should return true to URLs with hashes
     */
    public function testValidUrlWithHash() {
        $value = 'http://spaghettiphp.org/download#download';
        $this->assertTrue(Validation::url($value));
    }

    /**
     * @testdox url should return false to URLs without protocol
     */
    public function testInvalidUrlWithoutPrefix() {
        $value = 'spaghettiphp.org/download#download';
        $this->assertFalse(Validation::url($value));
    }

    /**
     * @testdox time should return true to 24 hours format
     */
    public function testValidTimeWith24Hours() {
        $value = '18:00';
        $this->assertTrue(Validation::time($value));
    }

    /**
     * @testdox time should return true to 12 hours format and leading zero
     */
    public function testValidTimeWith12Hours() {
        $value = '06:00pm';
        $this->assertTrue(Validation::time($value));
    }
    
    /**
     * @testdox time should return true to 12 hours format without leading zero
     */
    public function testValidTimeWith12Hours2() {
        $value = '6:00pm';
        $this->assertTrue(Validation::time($value));
    }

    /**
     * @testdox email should return true to valid email
     */
    public function testValidEmail() {
        $value = 'spaghettiphp@spaghettiphp.org';
        $this->assertTrue(Validation::email($value));
    }

    /**
     * @testdox email should return false to invalid email
     */
    public function testInvalidEmail() {
        $value = 'spaghettiphp.spaghettiphp.org';
        $this->assertFalse(Validation::email($value));
    }

    /**
     * @testdox date should return true to valid date
     */
    public function testValidDate() {
        $value = '01/01/2009';
        $this->assertTrue(Validation::date($value));
    }
}
