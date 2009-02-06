<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class RenderViewTask extends Task {
    public $template = "";
    public $outputFile = "";
    public $outputDir = "";
    public $data = array();
    private $content = array();
    public function execute() {
        $view = new View;
        $content = $view->renderView(App::path("Script", "templates/{$this->template}", "phtm"), $this->data);
        return "<?php\n\n{$content}\n\n?>";
    }
}

?>