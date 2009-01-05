<?php
class ModelCommand extends Command {
    public $tasks = array("CreateView");
    function execute($modelName = null, $fields = null){
	
		if(is_null($modelName)):
			$this->error("model name can't be blank");
		endif;
	
		$modelName = Inflector::camelize($modelName);
		$fileName = Inflector::underscore($modelName).".php";
		$fields = (is_null($fields))? array() : array($fields);
		
		$model = $this->CreateView;
		$model->template = "model";
		$model->outputDir = "app/models/";
		$model->outputFile = $fileName;
		$model->data = array("modelName"=>$modelName);
		$model->execute();
    }
}
?>