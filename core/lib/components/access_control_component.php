<?php
/**
 *  Short description.
 *
 *  @author	   José Cláudio Medeiros de Lima <contato@claudiomedeiros.net>
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2009, José Cláudio Medeiros de Lima <contato@claudiomedeiros.net>
 *
 */

class AccessControlComponent extends Component {
    public function initialize(&$controller) {
        if(!isset($controller->AuthComponent)):
            trigger_error("Controller::AuthComponent not found", E_USER_ERROR);
        endif;
    }
}

?>