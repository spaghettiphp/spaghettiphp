<?php
class ControllerCommand extends Command {
    public $tasks = array("CreateView");
    function execute($controllerName = null, $methods = null){
		
		// controller name can't be blank
		if(is_null($controllerName)):
			$this->error("controller name can't be blank");
		endif;
	
		// camelizes the controller name
		$controllerName = Inflector::camelize($controllerName);
		
		// creates the controller's filename
		$fileName = Inflector::underscore($controllerName)."_controller.php";
		
		// defines the controller's views directory
		$viewsDirectory = "app/views/" . Inflector::underscore($controllerName) . "/";
		
		// creates the methods specified on the command line
		$methods = (is_null($methods))? array("index") : explode(",",$methods);
		$methods = array_map(array("Inflector","underscore"), $methods);
		
		// creates the controller file from the template file
		$controller = $this->CreateView;
		$controller->template = "controller";
		$controller->outputDir = "app/controllers/";
		$controller->outputFile = $fileName;
		$controller->data = array("controllerName"=>$controllerName, "methods"=>$methods);
		$controller->execute();
		
		// creates the directory in the view folder
		$Folder = new Folder($viewsDirectory);
		if($Folder->exists()):
		    $this->log($viewsDirectory, "exists");
		else:
		    $Folder->save();
		    $this->log($viewsDirectory, "created");
		endif;
		
		// creates the view files for methods
		foreach($methods as $method):
			$methodView = $this->CreateView;
			$methodView->template = "view";
			$methodView->outputDir = $viewsDirectory;
			$methodView->outputFile = "{$method}.phtm";
			$methodView->execute();
		endforeach;
    }
}
?>