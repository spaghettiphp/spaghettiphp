<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
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
        return $this->output($this->tag("button", $submit, array_merge(array("type" => "submit"), $attr)));
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
    public function hidden($name = "", $value = "", $attr = array()) {
        return $this->output($this->openTag("input", array_merge(array("name" => $name, "value" => $value, "type" => "hidden"), $attr), false));
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
	public function dateselect($name="date",$start_year=1980,$end_year=null,$current_month=null,$current_day=null,$current_year=null)
	{
		//if default values are not passed, default values should be the current date
		$year_now = (int)date("Y");
		$day_now = (int)date("d");
		$month_now = (int)date("m");
		if(!$end_year) $end_year = $year_now;
		if(!$current_year) $current_year = $year_now;
		if(!$current_month) $current_moth = $month_now;
		if(!$current_day) $current_day = $day_now;
		
		//day select
		$select_day = '<select name="'.$name.'[d]" id="'.$name.'_day">';
		
		//day select options
		for($i=1;$i<32;$i++):
			$select_day .= '<option value="'.$i.'"';
			if($i==$current_day) $select_day .= ' selected="selected"';
			$select_day .= '>'.$i.'</option>';
		endfor;
		
		$select_day .= "</select>";
		
		//month select
		$select_month = '<select name="'.$name.'[m]" id="'.$name.'_month">';
		
		//month select options
		for($i=1;$i<13;$i++):
			$select_month .= '<option value="'.$i.'"';
			if($i==$current_month) $select_month .= ' selected="selected"';
			$select_month .= '>'.$i.'</option>';
		endfor;
		
		$select_month .= "</select>";
		
		//year select
		$select_year = '<select name="'.$name.'[y]" id="'.$name.'_year">';
		
		//year select options
		for($i=$start_year;$i<$end_year+1;$i++):
			$select_year .= '<option value="'.$i.'"';
			if($i==$current_year) $select_year .= ' selected="selected"';
			$select_year .= '>'.$i.'</option>';
		endfor;
		
		$select_year .= "</select>";
		
		return $select_day.$select_month.$select_year;
	}
}

?>