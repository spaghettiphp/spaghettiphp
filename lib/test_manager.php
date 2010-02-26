<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class TestManager extends Object {
    public static function loadTestFramework() {
        App::import("Core", array("../vendors/simpletest/unit_tester", "../vendors/simpletest/mock_objects", "../vendors/simpletest/web_tester"));
    }
    public static function runTestCase($testCase = "") {
        $testGroup = new GroupTest("Individual Test Case: " . Inflector::humanize($testCase));
        $testGroup->addTestFile(App::path("Core", "tests/cases/{$testCase}.test"));
        $testGroup->run(new HtmlReporter);
    }
    public static function runGroupTest($groupTest = "") {
        $groupTestFile = Inflector::underscore($groupTest);
        App::import("Core", "tests/groups/{$groupTestFile}.group");
        $groupTestClass = "{$groupTest}GroupTest";
        $groupTestObject = new $groupTestClass;
        $groupTestObject->run(new HtmlReporter);
    }
}

?>