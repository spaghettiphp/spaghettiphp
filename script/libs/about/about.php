<?php
echo PHP_EOL;
echo "Informações sobre a aplicação e ambiente" . PHP_EOL;
echo "Spaghetti* Framework" . PHP_EOL . PHP_EOL;

$config["databases"] = Config::read("database");
$config["environment"] = Config::read("environment");
$config["database"] = $config["databases"][$config["environment"]];

printf("%-35s  %s\n", "Versão do PHP", phpversion());
printf("%-35s  %s\n", "Driver de banco de dados", $config["database"]["driver"]);

echo PHP_EOL;
?>