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
 *
 */

class HtmlHelper extends Helper {
    public function openTag($tag = "", $attr = "", $close = true) {
        $html = "<{$tag}";
        if(($attr = $this->attr($attr)) != ""):
            $html .= " $attr";
        endif;
        $html .= ($close ? "" : " /") . ">";
        return $html;
    }
    public function closeTag($tag = "") {
        return "</{$tag}>";
    }
    public function tag($tag = "", $content = "", $attr = array(), $close = true) {
        $html = $this->openTag($tag, $attr, $close);
        if($close):
            $html .= "{$content}" . $this->closeTag($tag);
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
    public function link($text = "", $url = "", $attr = array(), $full = false) {
        if(!is_array($attr)):
            $attr = array();
        endif;
        $href = array("href" => Mapper::url($url, $full));
        $attr = array_merge($href, $attr);
        return $this->output($this->tag("a", $text, $attr));
    }
    public function image($src = "", $alt = "", $attr = array(), $full = false) {
        if(!is_array($attr)):
            $attr = array();
        endif;
        $src_alt = array("src" => Mapper::url("/images/{$src}", $full), "alt" => $alt);
        $attr = array_merge($src_alt, $attr);
        return $this->output($this->tag("img", null, $attr, false));
    }
    public function stylesheet($href = "", $attr = array(), $full = false) {
        $tags = "";
        if(is_array($href)):
            foreach($href as $tag):
                $tags .= HtmlHelper::stylesheet($tag, $attr, $full) . PHP_EOL;
            endforeach;
            return $tags;
        endif;
        $attrs = array("href" => Mapper::url("/styles/{$href}", $full), "rel" => "stylesheet", "type" => "text/css");
        $attr = array_merge($attrs, $attr);
        return $this->output($this->tag("link", null, $attr, false));
    }
    public function script($src = "", $attr = array(), $full = false) {
        $tags = "";
        if(is_array($src)):
            foreach($src as $tag):
                $tags .= HtmlHelper::script($tag, $attr, $full) . PHP_EOL;
            endforeach;
            return $tags;
        endif;
        $attrs = array("src" => Mapper::url("/scripts/{$src}", $full), "type" => "text/javascript");
        $attr = array_merge($attrs, $attr);
        return $this->output($this->tag("script", null, $attr));
    }
}

?>