<?php

class Dispatcher extends Object {
    public static function dispatch() {
        $path = Mapper::parse();
        $path['controller'] = Inflector::hyphenToUnderscore($path['controller']);
        $path['action'] = Inflector::hyphenToUnderscore($path['action']);
        $controller_name = Inflector::camelize($path['controller']) . 'Controller';
        $view_path = $path['controller'] . '/' . $path['action'] . '.' . $path['extension'];
        if(Loader::exists('Controller', $controller_name)):
            $controller =& Loader::instance('Controller', $controller_name);
            if(!can_call_method($controller, $path['action']) && !Loader::exists('View', $view_path)):
                $controller->error('missingAction', array(
                    'controller' => $path['controller'],
                    'action' => $path['action']
                ));
                return false;
            endif;
        else:
            if(Loader::exists('View', $view_path)):
                $controller =& Loader::instance('Controller', 'AppController');
            else:
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
        if(in_array($path['action'], $controller->methods) && can_call_method($controller, $path['action'])):
            $params = $path['params'];
            if(!is_null($path['id'])) $params = array_merge(array($path['id']), $params);
            call_user_func_array(array(&$controller, $path['action']), $params);
        endif;
        if($controller->autoRender):
            $controller->render();
        endif;
        $controller->componentEvent('shutdown');
        $output = $controller->output;
        $controller->afterFilter();
        return $output;
    }
}