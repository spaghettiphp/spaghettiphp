<?php
/**
 *  Geração automática do formulário em HTML de acordo com os dados passados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

App::import("Helper", "html_helper");

class FormHelper extends HtmlHelper {
    /**
     *  Retorna um elemento HTML do formulário formatado.
     * 
     *  @param string $action Ação atual do modelo
     *  @param array $options Atributos e opções da tag HTML
     *  @return string Tag FORM aberto e formatado
     */
    public function create($action = null, $options = array()) {
        $attributes = array_merge(
            array(
                "method" => "post",
                "action" => Mapper::url($action)
            ),
            $options
        );
        if($attributes["method"] == "file"):
            $attributes["method"] = "post";
            $attributes["enctype"] = "multipart/form-data";
        endif;
        return $this->output($this->openTag("form", $attributes));
    }
    /**
     *  Fecha um elemento HTML do formulário de acordo com os atributos repassados.
     *
     *  @param boolean $submit Botão e envio do formulário
     *  @param array $attributes Atributos e opções da tag HTML
     *  @return string Tag FORM fechada
     */
    public function close($submit = null, $attributes = array()) {
        $form = $this->closeTag("form");
        if(!is_null($submit)):
            $form = $this->submit($submit, $attributes) . $form;
        endif;
        return $this->output($form);
    }
    /**
     *  Cria um botão de envio dos dados do formulário.
     *
     *  @param string $submit Nome do botão de envio
     *  @param array $attributes Atributos e opções da tag
     *  @return string Botão de envio do formulário
     */
    public function submit($text, $attributes = array()) {
        $attributes = array_merge(
            array(
                "type" => "submit",
                "tag" => "button"
            ),
            $attributes
        );
        if(array_unset($attributes, "tag") == "input"):
            $attributes["value"] = $text;
            $button = $this->tag("input", null, $attributes, false);
        else:
            $button = $this->tag("button", $text, $attributes);
        endif;
        return $this->output($button);
    }
    /**
     *  Cria uma caixa de seleção.
     * 
     *  @param string $name Nome da caixa de seleção
     *  @param array $options Atributos da tag
     *  @return string Caixa de seleção do formulário
     */
    public function select($name, $options = array()) {
        $options = array_merge(array(
            "name" => $name,
            "options" => array(),
            "value" => null
        ), $options);
        $selectOptions = array_unset($options, "options");
        $content = "";
        foreach($selectOptions as $key => $value):
            $optionAttr = array("value" => $key);
            if($key === $options["value"]):
                $optionAttr["selected"] = true;
                unset($options["value"]);
            endif;
            $content .= $this->tag("option", $value, $optionAttr);
        endforeach;
        return $this->output($this->tag("select", $content, $options));
    }
    /**
     *  Cria um input radio.
     *
     *  @param string $name Nome do input
     *  @param array $options Atributos da tag
     *  @return string Input do formulário
     */
    public function radio($name, $options = array()) {
        $options = array_merge(array(
            "options" => array(),
            "value" => null
        ), $options);
        $content = "";
        $radioOptions = array_unset($options, "options");
        foreach($radioOptions as $key => $value):
            $radioAttr = array(
                "type" => "radio",
                "value" => $key,
                "id" => Inflector::camelize("{$name}_{$key}"),
                "name" => $name,
            );
            if($key === $options["value"]):
                $radioAttr["checked"] = true;
                unset($options["value"]);
            endif;
            $content .= $this->tag("input", null, $radioAttr, false);
            $content .= $this->tag("label", $value, array("for" => $radioAttr["id"]));
        endforeach;
        return $this->output($content);
    }
    /**
     *  Cria caixa de entrada formatada e com label.
     * 
     *  @param string $name Nome do campo de entrada
     *  @param array $options Atributos da tag
     *  @return string Campo de entrada do formulário
     */
    public function input($name, $options = array()) {
        $options = array_merge(array(
            "name" => $name,
            "type" => "text",
            "id" => Inflector::camelize("form_" . Inflector::slug($name)),
            "label" => Inflector::humanize($name),
            "div" => true
        ), $options);
        $label = array_unset($options, "label");
        $div = array_unset($options, "div");
        if($options["type"] == "select"):
            $selectOptions = $options;
            unset($selectOptions["type"]);
            $input = $this->select($name, $selectOptions);
        elseif($options["type"] == "textarea"):
            $input = $this->tag("textarea", array_unset($options, "value"), $options);
        elseif($options["type"] == "radio"):
            $label = false;
            $input = $this->radio($name, $options);
        else:
            if($options["type"] == "hidden"):
                $div = $label = false;
            elseif($name == "password"):
                $options["type"] = "password";
            endif;
            $input = $this->tag("input", null, $options, false);
        endif;
        if($label):
            $input = $this->tag("label", $label, array("for" => $options["id"])) . $input;
        endif;
        if($div):
            if($div === true):
                $div = "input {$options['type']}";
            endif;
            $input = $this->div($input, $div);
        endif;
        return $this->output($input);
    }
    /**
     *  Cria um conjunto de caixa de seleção para a data.
     * 
     *  @param string $name Nome do conjunto de caixas de seleção
     *  @param int $start_year Ano inicial da seleção
     *  @param int $end_year Ano final da seleção
     *  @param int $current_month Mês corrente
     *  @param int $current_day Dia corrente
     *  @param int $current_year Ano corrente
     *  @return string Retorna um conjunto de caixa de seleção
     */
    public function dateselect($name = "date", $start_year = 1980, $end_year = null, $current_month = null, $current_day = null, $current_year = null) {
        //if default values are not passed, default values should be the current date
        $year_now = (int) date("Y");
        $day_now = (int) date("d");
        $month_now = (int) date("m");
        if(!$end_year) $end_year = $year_now;
        if(!$current_year) $current_year = $year_now;
        if(!$current_month) $current_month = $month_now;
        if(!$current_day) $current_day = $day_now;

        //day select
        $select_day = '<select name="' . $name . '[d]" id="' . $name . '_day">';

        //day select options
        for($i = 1; $i < 32; $i++):
            $select_day .= '<option value="' . $i . '"';
            if($i == $current_day) $select_day .= ' selected="selected"';
            $select_day .= '>' . $i . '</option>';
        endfor;

        $select_day .= "</select>";

        //month select
        $select_month = '<select name="' . $name . '[m]" id="' . $name . '_month">';

        //month select options
        for($i = 1; $i < 13; $i++):
            $select_month .= '<option value="' . $i . '"';
            if($i == $current_month) $select_month .= ' selected="selected"';
            $select_month .= '>' . $i . '</option>';
        endfor;

        $select_month .= "</select>";

        //year select
        $select_year = '<select name="' . $name . '[y]" id="' . $name . '_year">';

        //year select options
        for($i = $start_year; $i < $end_year + 1; $i++):
            $select_year .= '<option value="' . $i . '"';
            if($i == $current_year) $select_year .= ' selected="selected"';
            $select_year .= '>' . $i . '</option>';
        endfor;

        $select_year .= "</select>";

        return $select_day . $select_month . $select_year;
	}
}

?>