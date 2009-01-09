<?php
/**
 *  Put description here.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Script.Tasks.RenderView
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
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
        $content = $view->renderView(App::exists("Script", "templates/{$this->template}", "phtm"), $this->data);
        return "<?php\n\n{$content}\n\n?>";
    }
}

?>