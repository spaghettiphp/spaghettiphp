<?php

class Dispatcher {
    public static function dispatch($request = null) {
        $request = self::normalize($request);
        $class = $controller_name = Inflector::camelize($request['controller']) . 'Controller';
        
        if(!Loader::exists('Controller', $controller_name)):
            $class = 'AppController';
        endif;
        $controller = Loader::instance('Controller', $class);
        
        if($controller->hasAction($request['action']) || self::hasView($request)):
            return $controller->callAction($request);
        elseif(get_class($controller) == 'AppController'):
            throw new MissingControllerException(array(
                'controller' => $controller_name
            ));
        else:
            // @todo maybe MissingActionException should be thrown by Controller
            throw new MissingActionException(array(
                'controller' => $controller_name,
                'action' => $request['action']
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
    // @todo this should not be in the Dispatcher
    protected static function hasView($request) {
        return Loader::exists('View', $request['controller'] . '/' . $request['action'] . '.' . $request['extension']);
    }
}