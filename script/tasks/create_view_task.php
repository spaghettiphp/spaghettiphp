<?php
class CreateViewTask extends Task {
    public $template = "";
    public $outputFile = "";
    public $outputDir = "";
    public $data = array();
    private $content = array();
    
    public function execute(){
	$Template = new Template();
	$content = $Template->render($this->template, $this->data);
	
	$File = new File($this->outputDir, $this->outputFile, $content);
	if($File->exists()):
	    $File->save();
	    $this->log("{$this->outputDir}{$this->outputFile}", "updated");
	else:
	    $File->save();
	    $this->log("{$this->outputDir}{$this->outputFile}", "created");
	endif;
    }
}
?>