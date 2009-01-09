<?php

class RenderViewTask extends Task {
    public $template = "";
    public $outputFile = "";
    public $outputDir = "";
    public $data = array();
    private $content = array();
    public function execute() {
		$view = new View;
		$content = $view->renderView(App::exists("Script", "templates/{$this->template}", "phtm"), $this->data);
		return "<?php\n\n{$content}\n\n?>";
    }
}

?>