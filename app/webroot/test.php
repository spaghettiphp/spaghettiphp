<?php

include "../../spaghetti.php";

App::import("Core", "test_manager");

TestManager::loadTestFramework();
TestManager::runTestCase($_GET["case"]);

?>