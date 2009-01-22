<?php
/**
 *  Grupo contendo todos os testes do Spaghetti.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Tests.Groups.All
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

class AllGroupTest extends GroupTest {
    public function AllGroupTest() {
        $this->addTestFile(App::exists("Core", "tests/cases/inflector.test"));
        $this->addTestFile(App::exists("Core", "tests/cases/dispatcher.test"));
        $this->addTestFile(App::exists("Core", "tests/cases/mapper.test"));
        $this->addTestFile(App::exists("Core", "tests/cases/controller.test"));
    }
}

?>