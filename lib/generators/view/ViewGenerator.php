<?php

class ViewGenerator extends Generator {
    public function start() {
        $args = func_get_args();
        $controller = Inflector::underscore(array_shift($args));
        $views = $args;

        $template_dir = 'lib/generators/view/templates';
        $view_template = $template_dir . '/view.php';
        
        if(!empty($views)):
            $view_dir = 'app/views/' . $controller;
            $this->createDir($view_dir);
            foreach($args as $action):
                $view = $view_dir . '/' . $action . '.htm.php';
                $this->renderTemplate($view_template, $view);
            endforeach;
        endif;
        
        return true;
    }
}