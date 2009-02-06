<?php
/**
 *  Grupo de Testes para todos os testes do Spaghetti*
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class AllGroupTest extends GroupTest {
    public function AllGroupTest() {
        $this->addTestFile(App::path("Core", "tests/cases/class_registry.test"));
        $this->addTestFile(App::path("Core", "tests/cases/component.test"));
        $this->addTestFile(App::path("Core", "tests/cases/controller.test"));
        $this->addTestFile(App::path("Core", "tests/cases/dispatcher.test"));
        $this->addTestFile(App::path("Core", "tests/cases/helper.test"));
        $this->addTestFile(App::path("Core", "tests/cases/inflector.test"));
        $this->addTestFile(App::path("Core", "tests/cases/mapper.test"));
        $this->addTestFile(App::path("Core", "tests/cases/model.test"));
        $this->addTestFile(App::path("Core", "tests/cases/utils.test"));
        $this->addTestFile(App::path("Core", "tests/cases/view.test"));
    }
}

?>