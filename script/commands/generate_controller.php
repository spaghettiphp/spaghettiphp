<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Script.Generate.Controller
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 */

class GenerateController extends Command {
    public $tasks = array("RenderView");
    public function execute($name = null) {
        if(is_null($name)):
            $this->error("controller name can't be blank");
        endif;
        
        $fileName = "{$name}_controller";
        $controllerName = Inflector::camelize($fileName);
        $baseController = $controllerName == "AppController" ? "Controller" : "AppController";
        
        $methods = array_slice(func_get_args(), 1);
        if(empty($methods)):
            $methods = array("index");
        endif;

        //$folder = new Folder("app/views/{$name}");
        //if($folder->isDir()):
        //    $this->log("app/views/{$name}", "exists");
        //else:
        //    $folder->mkdir();
        //    $this->log("app/views/{$name}", "created");
        //endif;
        
        $this->RenderView->template = "controller";
        $this->RenderView->data = array("name" => $controllerName, "methods" => $methods, "baseController" => $baseController);
        $content = $this->RenderView->execute();
        
        $file = fopen(APP . DS . "controllers" . DS . "{$fileName}.php", "w");
        fwrite($file, $content);
        fclose($file);
    }
}

?>