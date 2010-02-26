<?php
/**
 *  Short description.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class MailComponent extends Component {
    protected $controller;
    
    public function startup(&$controller) {
        $this->controller = $controller;
    }
}

?>