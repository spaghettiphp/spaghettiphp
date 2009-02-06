<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

ini_set("error_reporting", E_ALL);
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(dirname(dirname(__FILE__))));
define("BASE_URL", "http://" . $_SERVER["HTTP_HOST"]);
define("CORE", ROOT . DS . "core");
define("LIB", CORE . DS . "lib");
define("APP", ROOT . DS . "app");
$self = dirname($_SERVER["PHP_SELF"]);
while(in_array(basename($self), array("app", "core", "tests", "webroot"))):
    $self = dirname($self);
endwhile;
define("WEBROOT", $self);

include "../../core/bootstrap.php";

App::import("Core", "test_manager");

TestManager::loadTestFramework();
if(isset($_GET["case"])):
    TestManager::runTestCase($_GET["case"]);
elseif(isset($_GET["group"])):
    TestManager::runGroupTest($_GET["group"]);
endif;

?>