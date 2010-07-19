<?php

class Dispatcher {
    public static function dispatch($request = null) {
        $request = self::normalize($request);
        $class = Inflector::camelize($request['controller']) . 'Controller';
        
        if(Loader::exists('Controller', $class)):
            $controller = Loader::instance('Controller', $class);
            return $controller->callAction($request);
            
        elseif(Controller::hasViewForAction($request)):
            $controller = Loader::instance('Controller', 'AppController');
            return $controller->render(View::path($request));

        else:
            throw new MissingControllerException(array(
                'controller' => $class
            ));
        endif;
    }
    protected static function normalize($request) {
        if(is_null($request)):
            $request = Mapper::parse();
        endif;
        $request['controller'] = Inflector::hyphenToUnderscore($request['controller']);
        $request['action'] = Inflector::hyphenToUnderscore($request['action']);
        
        return $request;
    }
}