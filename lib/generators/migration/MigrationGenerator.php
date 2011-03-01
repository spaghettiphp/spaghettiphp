<?php

class MigrationGenerator extends Generator {
    public function start($migration) {
        $migration = Inflector::underscore($migration);

        $this->generateMigration($migration);
    }

    protected function generateMigration($migration) {
        $version = date('YmdHis');
        $migration = $version . '_' . $migration . '.sql';

        $this->createDir('db/migrations');
        $this->log('created', 'db/migrations/' . $migration);
        Filesystem::write('db/migrations/' . $migration, '');
    }
}