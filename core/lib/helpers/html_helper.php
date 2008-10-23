<?php
/**
 *  Put description here
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Lib.Helper.Html
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class HtmlHelper extends Helper {
    public function tag($tag = "", $content = "", $attr = array(), $close = true) {
        $html = "<{$tag}";
        if(($attr = $this->attr($attr)) != ""):
            $html .= " $attr";
        endif;
        if($close):
            $html .= ">{$content}</{$tag}>";
        else:
            $html .= " />";
        endif;
        return $html;
    }
    public function attr($attr = array()) {
        $attributes = array();
        if(is_array($attr)):
            foreach($attr as $name => $value):
                if($value === true):
                    $value = $name;
                endif;
                $attributes []= "$name=\"$value\"";
            endforeach;
        endif;
        return join(" ", $attributes);
    }
}

?>