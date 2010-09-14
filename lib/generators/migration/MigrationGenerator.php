<?php

class MigrationGenerator extends Generator {
    public function start() {
        $args = func_get_args();
        $migration = Inflector::underscore(array_shift($args));
        $version = date('YmdHis');
        $migration = $version . '_' . $migration . '.sql';
        
        $this->createDir('db/migrations');
        $this->log('created', 'db/migrations/' . $migration);
        Filesystem::write('db/migrations/' . $migration, '');

        return true;
    }
}