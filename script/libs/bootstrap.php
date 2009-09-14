<?php
define("DS", DIRECTORY_SEPARATOR);
define("ROOT", dirname(dirname(dirname(__FILE__))));
define("BASE_URL", "http://" . $_SERVER["HTTP_HOST"]);
define("CORE", ROOT . DS . "core");
define("LIB", CORE . DS . "lib");
define("APP", ROOT . DS . "app");

require_once CORE . DS . "bootstrap.php";

App::import("Core", array("shell_parser"));
?>