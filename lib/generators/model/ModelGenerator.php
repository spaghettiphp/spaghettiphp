<?php

class ModelGenerator extends Generator {
    public function start() {
        $args = func_get_args();
        $model = Inflector::underscore(array_shift($args));
        
        $template_dir = SPAGHETTI_ROOT . '/lib/generators/model/templates';
        $model_template = $template_dir . '/model.php';
        $destination = 'app/models/' . $model . '.php';
        
        $this->createDir('app/models');
        $this->renderTemplate($model_template, $destination, array(
            'model' => Inflector::camelize($model)
        ));
        
        return true;
    }
}