<?php

class View extends Object {
    public $autoLayout;
    public $data = array();
    public $helpers = array('Html');
    public $loadedHelpers = null;
    public $layout;
    public $pageTitle;
    public $params = array();
    
    public function loadHelpers() {
        $this->loadedHelpers = array();
        foreach($this->helpers as $helper):
            $class = $helper . 'Helper';
            $helper = Inflector::underscore($helper);
            $file = $helper . '_helper';
            if(!class_exists($class)):
                if(App::path('Helper', $file)):
                    App::import('Helper', $file);
                else:
                    $this->error('missingHelper', array('helper' => $class));
                    return false;
                endif;
            endif;
            $this->loadedHelpers[$helper] = new $class($this);
        endforeach;
        return $this->loadedHelpers;
    }
    public function renderView($filename, $data = array()) {
        if(is_null($this->loadedHelpers)):
            $this->loadHelpers();
        endif;
        extract($data, EXTR_OVERWRITE);
        extract($this->loadedHelpers, EXTR_PREFIX_SAME, 'helper_');
        ob_start();
        include $filename;
        $output = ob_get_clean();
        return $output;
    }
    public function render($action = null, $layout = null) {
        if(is_null($action)):
            $controller = $this->params['controller'];
            $action = $this->params['action'];
            $ext = $this->params['extension'];
            $layout = $this->layout;
        else:
            $filename = explode('.', $action);
            $controller = null;
            $action = $filename[0];
            $ext = $filename[1] ? $filename[1] : $this->params['extension'];
        endif;
        $file = App::path('View', $controller . '/' . $action . '.' . $ext);
        if($file):
            $output = $this->renderView($file, $this->data);
            $layout = is_null($layout) ? $this->layout : $layout;
            if($this->autoLayout && $layout):
                $output = $this->renderLayout($output, $layout, $ext);
            endif;
            return $output;
        else:
            $this->error('missingView', array(
                'controller' => $controller,
                'view' => $action,
                'extension' => $ext)
            );
            return false;
        endif;
    }
    public function renderLayout($content, $layout, $ext = null) {
        if(is_null($ext)):
            $ext = $this->params['extension'];
        endif;
        $file = App::path('Layout', $layout . '.' . $ext);
        if($file):
            $this->contentForLayout = $content;
            return $this->renderView($file, $this->data);
        else:
            $this->error('missingLayout', array(
                'layout' => $layout,
                'extension' => $ext
            ));
            return false;
        endif;        
    }
    public function element($element, $params = array()) {
        $element = dirname($element) . '/_' . basename($element);
        $ext = $this->params['extension'] ? $this->params['extension'] : Config::read('defaultExtension');
        return $this->renderView(App::path('View', $element . '.' . $ext), $params);
    }
}