<?php

class Dispatcher {
    public static function dispatch() {
        $path = Mapper::parse();
        $path['controller'] = Inflector::hyphenToUnderscore($path['controller']);
        $path['action'] = Inflector::hyphenToUnderscore($path['action']);
        $controller_name = Inflector::camelize($path['controller']) . 'Controller';
        $view_path = $path['controller'] . '/' . $path['action'] . '.' . $path['extension'];
        $view_exists = Loader::exists('View', $view_path);
        
        if(Loader::exists('Controller', $controller_name)):
            $controller = Loader::instance('Controller', $controller_name);
            if(!$controller->isAction($path['action']) && !$view_exists):
                $controller->error('missingAction', array(
                    'controller' => $path['controller'],
                    'action' => $path['action']
                ));
                return false;
            endif;
        else:
            $controller = Loader::instance('Controller', 'AppController');
            if(!$view_exists):
                $controller->error('missingController', array(
                    'controller' => $path['controller']
                ));
                return false;
            endif;
        endif;

        $controller->params = $path;
        $controller->componentEvent('initialize');
        $controller->beforeFilter();
        $controller->componentEvent('startup');

        if($controller->isAction($path['action'])):
            $params = $path['params'];
            if(!is_null($path['id'])):
                array_unshift($params, $path['id']);
            endif;
            call_user_func_array(array(&$controller, $path['action']), $params);
        endif;

        if($controller->autoRender):
            $controller->beforeRender();
            $output = $controller->render();
        endif;

        $controller->componentEvent('shutdown');
        $controller->afterFilter();

        return $output;
    }
}