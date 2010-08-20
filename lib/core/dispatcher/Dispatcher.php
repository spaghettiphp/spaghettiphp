<?php

class Dispatcher {
    public static function dispatch($request = null) {
        $request = self::normalize($request);
        
        try {
            $class = Inflector::camelize($request['controller']) . 'Controller';
            $controller = Controller::load($class, true);
            return $controller->callAction($request);
        }
        catch(MissingControllerException $e) {
            if(Controller::hasViewForAction($request)):
                $controller = new AppController();
                return $controller->render(View::path($request));
            else:
                throw $e;
            endif;
        }
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