<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Lib.Helper.Form
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class FormHelper extends HtmlHelper {
    public function create() {
        return $this->output($this->openTag("form"));
    }
}

?>