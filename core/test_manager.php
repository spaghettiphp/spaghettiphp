<?php

class TestManager extends Object {
    public function loadTestFramework() {
        App::import("Core", array("../vendors/simpletest/unit_tester", "../vendors/simpletest/mock_objects", "../vendors/simpletest/web_tester"));
    }
    public function runTestCase($testCase = "") {
        $testGroup =& new GroupTest("Test Case Individual: {$testCase}");
        $testGroup->addTestFile(App::exists("Core", "tests/cases/{$testCase}.test"));
        $testGroup->run(new HtmlReporter);
    }
    public function runGroupTest($groupTest = "") {
        $groupTestFile = Inflector::underscore($groupTest);
        App::import("Core", "tests/groups/{$groupTestFile}.group");
        $groupTestClass = "{$groupTest}GroupTest";
        $groupTestObject =& new $groupTestClass;
        $groupTestObject->run(new HtmlReporter);
    }
}

?>