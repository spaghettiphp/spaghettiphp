<?php

require dirname(dirname(__FILE__)) . '/config/bootstrap.php';
require 'config/settings.php';
require 'config/connections.php';

function get_migrations() {
    $migrations = Filesystem::getFiles('db/migrations');
    natsort($migrations);
    return $migrations;
}

function get_migration_version($migration) {
    return substr($migration, 0, 14);
}

function get_migration_name($migration) {
    return Inflector::camelize(substr(Filesystem::filename($migration), 15));
}

function create_schema_migrations($connection) {
    $sql = <<<EOT
CREATE TABLE `schema_migrations` (
  `version` varchar(255),
  PRIMARY KEY (`version`)
)
ENGINE = InnoDB
CHARACTER SET utf8 COLLATE utf8_general_ci;
EOT;
    $connection->query($sql);
}

function should_migrate($migration, $connection) {
    $ext = Filesystem::extension($migration);
    if(!in_array($ext, array('php', 'sql'))) {
        return false;
    }

    $should_migrate = $connection->count(array(
        'table' => 'schema_migrations',
        'conditions' => array(
            'version' => get_migration_version($migration)
        )
    ));
    return !$should_migrate;
}

function migrate($migration, $connection) {
    echo 'importing ' . $migration . '... ';

    $ext = Filesystem::extension($migration);
    if($ext == 'php') {
        require_once 'db/migrations/' . $migration;
        $classname = get_migration_name($migration);
        $classname::migrate($connection);
    }
    else {
        $connection->query(utf8_decode(Filesystem::read('db/migrations/' . $migration)));
    }
    $connection->create(array(
        'table' => 'schema_migrations',
        'values' => array(
            'version' => get_migration_version($migration)
        )
    ));

    echo 'done' . PHP_EOL;
}

$connection = Connection::get(Config::read('App.environment'));
if(!in_array('schema_migrations', $connection->listSources())) {
    create_schema_migrations($connection);
}

$migrations = get_migrations();
foreach($migrations as $migration) {
    if(should_migrate($migration, $connection)) {
        migrate($migration, $connection);
    }
}