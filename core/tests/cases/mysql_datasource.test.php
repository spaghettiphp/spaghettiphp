<?php
/**
 *  Test Case de MysqlDatasource
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class TestMysqlDatasource extends UnitTestCase {
    public function setUp() {
        $this->datasource = new MysqlDatasource();
    }
    public function tearDown() {
        $this->datasource = null;
    }
    
}

?>