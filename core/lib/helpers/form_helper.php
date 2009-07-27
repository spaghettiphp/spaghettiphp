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
    public function create($action = null, $options = array()) {
        $attr = array_merge(array("method" => "post", "action" => Mapper::url($action)), $options);
        $form = $this->openTag("form", $attr);
        return $this->output($form);
    }
    public function close($submit = null, $attr = array()) {
        $form = $this->closeTag("form");
        if($submit != null):
            $form = $this->submit($submit, $attr) . $form;
        endif;
        return $this->output($form);
    }
    public function submit($submit = "", $attr = array()) {
        return $this->output($this->openTag("input", array_merge(array("name" => $name, "value" => $submit, "type" => "submit"), $attr), false));
    }
    public function text($name = "", $value = "", $attr = array()) {
        return $this->output($this->openTag("input", array_merge(array("name" => $name, "value" => $value, "type" => "text"), $attr), false));
    }
    public function textarea($name = "", $value = "", $attr = array()) {
        return $this->output($this->tag("textarea", $value, array_merge(array("name" => $name), $attr)));
    }
    public function password($name = "", $value = "", $attr = array()) {
        return $this->output($this->openTag("input", array_merge(array("name" => $name, "value" => $value, "type" => "password"), $attr), false));
    }
    public function file($name = "", $attr = array()) {
        return $this->output($this->openTag("input", array_merge(array("name" => $name, "type" => "file"), $attr), false));
    }
    public function select($name = "", $values = array(), $selected = "", $attr = array()) {
        $options = "";
        foreach($values as $key => $value):
            $optionAttr = array("value" => $key);
            if($key == $selected):
                $optionAttr["selected"] = "selected";
            endif;
            $options .= $this->tag("option", $value, $optionAttr);
        endforeach;
        return $this->tag("select", $options, array_merge(array("name" => $name), $attr));
    }
    public function input($name = "", $value = "", $options = array()) {
        $options = array_merge(array(
            "type" => "text",
            "label" => Inflector::humanize($name)
        ), $options);
        $type = $options["type"];
        $label = $options["label"];
        unset($options["type"]);
        unset($options["label"]);
        if($type == "select"):
            $values = $options["options"];
            unset($options["options"]);
            $input = $this->select($name, $values, $value, $options);
        elseif($type == "file"):
            $input = $this->file($name, $options);
        else:
            $input = $this->{$type}($name, $value, $options);
        endif;
        return $label != false ? $this->tag("label", "{$label}\n{$input}") : $input;
    }
}

?>