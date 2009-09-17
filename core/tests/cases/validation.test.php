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
}

?>