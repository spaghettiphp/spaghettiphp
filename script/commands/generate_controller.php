<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class GenerateController extends Command {
    public $tasks = array("RenderView");
    public function execute($name = null) {
        if(is_null($name)):
            $this->error("controller name can't be blank");
        endif;
        
        $filename = "{$name}_controller";
        $controllerName = Inflector::camelize($filename);
        $baseController = $controllerName == "AppController" ? "Controller" : "AppController";
        
        $methods = array_slice(func_get_args(), 1);
        if(empty($methods)):
            $methods = array("index");
        endif;

        $this->RenderView->template = "controller";
        $this->RenderView->data = array("name" => $controllerName, "methods" => $methods, "baseController" => $baseController);
        $content = $this->RenderView->execute();

        $filepath = "app/controllers/{$filename}.php";
        $this->log($filepath, File::exists($filepath) ? "modified" : "created");
        $file = new File($filepath, "w");
        $file->write($content);

        $folder = new Folder("app/views/{$name}");
        if($folder->isDir()):
            $this->log("app/views/{$name}", "exists");
        else:
            $folder->mkdir();
            $this->log("app/views/{$name}", "created");
        endif;
        
        foreach($methods as $method):
            $viewFile = "app/views/{$name}/{$method}.phtm";
            if(File::exists($viewFile)):
                $this->log($viewFile, "exists");
            else:
                $this->log($viewFile, "created");
                $file = new File($viewFile, "w");
            endif;
        endforeach;
    }
}

?>