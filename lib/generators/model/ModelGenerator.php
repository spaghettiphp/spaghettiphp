<?php

class ModelGenerator extends Generator {
    public function start($model) {
        $model = Inflector::underscore($model);
        
        $this->generateModel($model);
        $this->generateMigration($model);
    }
    
    protected function generateModel($model) {
        $template_dir = 'lib/generators/model/templates';
        $model_template = $template_dir . '/model.php';
        $destination = 'app/models/' . $model . '.php';
        
        $this->createDir('app/models');
        $this->renderTemplate($model_template, $destination, array(
            'model' => Inflector::camelize($model)
        ));
    }
    
    protected function generateMigration($model) {
        $migration = 'create_' . $model;
        self::invoke('migration', array($migration));
    }
}