<?php

class ControllerGenerator extends Generator {
    public function start() {
        $args = func_get_args();
        $controller = Inflector::underscore(array_shift($args));

        $template_dir = 'lib/generators/controller/templates';
        $controller_template = $template_dir . '/controller.php';
        $view_template = $template_dir . '/view.php';
        $destination = 'app/controllers/' . $controller . '_controller.php';

        $this->createDir('app/controllers');
        $this->renderTemplate($controller_template, $destination, array(
            'controller' => Inflector::camelize($controller),
            'actions' => $args
        ));

        if(!empty($args)) {
            $view_dir = 'app/views/' . $controller;
            $this->createDir($view_dir);
            foreach($args as $action) {
                $view = $view_dir . '/' . $action . '.htm.php';
                $this->renderTemplate($view_template, $view);
            }
        }

        return true;
    }
}