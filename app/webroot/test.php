<?php

include "../../spaghetti.php";

App::import("Core", "test_manager");

TestManager::loadTestFramework();
if(isset($_GET["case"]))
    TestManager::runTestCase($_GET["case"]);
elseif(isset($_GET["group"]))
    TestManager::runGroupTest($_GET["group"]);

?>