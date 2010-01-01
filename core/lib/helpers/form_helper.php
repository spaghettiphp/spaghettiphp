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
        $options += array(
            "method" => "post",
            "action" => Mapper::url($action)
        );
        if($options["method"] == "file"):
            $options["method"] = "post";
            $options["enctype"] = "multipart/form-data";
        endif;
        return $this->openTag("form", $options);
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
        return $form;
    }
    /**
     *  Cria um botão de envio dos dados do formulário.
     *
     *  @param string $submit Nome do botão de envio
     *  @param array $attributes Atributos e opções da tag
     *  @return string Botão de envio do formulário
     */
    public function submit($text, $attributes = array()) {
        $attributes += array(
            "type" => "submit",
            "tag" => "button"
        );
        switch(array_unset($attributes, "tag")):
            case "image":
                $attributes["alt"] = $text;
                $attributes["type"] = "image";
                if(!$this->external($attributes["src"])):
                    $attributes["src"] = Mapper::url("/images/" . $attributes["src"]);
                endif;
            case "input":
                $attributes["value"] = $text;
                return $this->tag("input", null, $attributes, false);
                break;
            default:
                return $this->tag("button", $text, $attributes);
        endswitch;
    }
    /**
     *  Cria uma caixa de seleção.
     * 
     *  @param string $name Nome da caixa de seleção
     *  @param array $options Atributos da tag
     *  @return string Caixa de seleção do formulário
     */
    public function select($name, $options = array()) {
        $options += array(
            "name" => $name,
            "options" => array(),
            "value" => null,
            "empty" => false
        );
        $selectOptions = array_unset($options, "options");
        $selectValue = array_unset($options, "value");
        if(($empty = array_unset($options, "empty")) !== false):
            $keys = array_keys($selectOptions);
            if(is_array($empty)):
                $emptyKeys = array_keys($empty);
                $key = $emptyKeys[0];
                $values = array_merge($empty, $selectOptions);
            else:
                $key = $empty;
                $values = array_merge(array($empty), $selectOptions);
            endif;
            array_unshift($keys, $key);
            $selectOptions = array_combine($keys, $values);
        endif;
        $content = "";
        foreach($selectOptions as $key => $value):
            $option = array("value" => $key);
            if((string) $key === (string) $selectValue):
                $option["selected"] = true;
            endif;
            $content .= $this->tag("option", $value, $option);
        endforeach;
        return $this->tag("select", $content, $options);

    }
    /**
     *  Cria um input radio.
     *
     *  @param string $name Nome do input
     *  @param array $options Atributos da tag
     *  @return string Input do formulário
     */
    public function radio($name, $options = array()) {
        $options += array(
            "options" => array(),
            "value" => null,
            "legend" => Inflector::camelize($name)
        );
        $content = "";
        $radioOptions = array_unset($options, "options");
        $radioValue = array_unset($options, "value");
        if($legend = array_unset($options, "legend")):
            $content = $this->tag("legend", $legend);
        endif;
        foreach($radioOptions as $key => $value):
            $radioAttr = array(
                "type" => "radio",
                "value" => $key,
                "id" => Inflector::camelize("{$name}_{$key}"),
                "name" => $name
            );
            if((string) $key === (string) $radioValue):
                $radioAttr["checked"] = true;
            endif;
            $for = array("for" => $radioAttr["id"]);
            $content .= $this->tag("input", null, $radioAttr, false);
            $content .= $this->tag("label", $value, $for);
        endforeach;
        $content = $this->tag("fieldset", $content);
        return $content;
    }
    /**
     *  Cria um conjunto de caixa de seleção para a data.
     * 
     *  @param string $name Nome do conjunto de caixas de seleção
     *  @param array $options Opções das caixas de seleção
     *  @return string Conjunto de caixa de seleção
     */
    public function date($name, $options = array()) {
        if(!is_null($options["value"])):
            if(is_array($options["value"])):
                $v = $options["value"];
                $options["value"] = "{$v['y']}-{$v['m']}-{$v['d']}";
            endif;
            $date = strtotime($options["value"]);
        else:
            $date = time();
        endif;
        $options += array(
            "value" => null,
            "startYear" => 1980,
            "endYear" => date("Y"),
            "currentDay" => date("j", $date),
            "currentMonth" => date("n", $date),
            "currentYear" => date("Y", $date),
            "format" => "dmy"
        );
        $days = array_range(1, 31);
        $months = array_range(1, 12);
        $years = array_range($options["startYear"], $options["endYear"]);
        $selectDay = $this->select($name . "[d]", array(
            "value" => $options["currentDay"],
            "options" => $days,
            "id" => $options["id"] . "D"
        ));
        $selectMonth = $this->select($name . "[m]", array(
            "value" => $options["currentMonth"],
            "options" => $months,
            "id" => $options["id"] . "M"
        ));
        $selectYear = $this->select($name . "[y]", array(
            "value" => $options["currentYear"],
            "options" => $years,
            "id" => $options["id"] . "Y"
        ));
        if($format == "ymd"):
            return $selectYear . $selectMonth . $selectDay;
        else:
            return $selectDay . $selectMonth . $selectYear;
        endif;
    }
    /**
     *  Cria caixa de entrada formatada e com label.
     * 
     *  @param string $name Nome do campo de entrada
     *  @param array $options Atributos da tag
     *  @return string Campo de entrada do formulário
     */
    public function input($name, $options = array()) {
        $options += array(
            "name" => $name,
            "type" => "text",
            "id" => Inflector::camelize("form_" . Inflector::slug($name)),
            "label" => Inflector::humanize($name),
            "div" => true
        );
        $label = array_unset($options, "label");
        $div = array_unset($options, "div");
        $type = $options["type"];
        switch($options["type"]):
            case "select":
                unset($options["type"]);
                $input = $this->select($name, $options);
                break;
            case "radio":
                $options["legend"] = $label;
                $label = false;
                $input = $this->radio($name, $options);
                break;
            case "date":
                $input = $this->date($name, $options);
                $options["id"] = $options["id"] . "D";
                break;
            case "textarea":
                unset($options["type"]);
                $value = Sanitize::html(array_unset($options, "value"));
                $input = $this->tag("textarea", $value, $options);
                break;
            case "hidden":
                $div = $label = false;
            default:
                if($name == "password"):
                    $options["type"] = "password";
                endif;
                $options["value"] = Sanitize::html($options["value"]);
                $input = $this->tag("input", null, $options, false);
        endswitch;
        if($label):
            $for = array("for" => $options["id"]);
            $input = $this->tag("label", $label, $for) . $input;
        endif;
        if($div):
            if($div === true):
                $div = "input {$type}";
            elseif(is_array($div)):
                $div += array("class" => "input {$options['type']}");
            endif;
            $input = $this->div($input, $div);
        endif;
        return $input;
    }
}

?>