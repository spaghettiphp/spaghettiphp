<?php
/**
 *  Geração automática do formulário em HTML de acordo com os dados passados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

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
     *  Cria uma caixa de texto.
     *
     *  @param string $name Nome da caixa de texto
     *  @param string $value Conteudo da caixa de texto
     *  @param array $attr Atributos da tag
     *  @return string Caixa de texto do formulário
     */
    public function text($name = "", $attributes = array()) {
        $attributes = array_merge(
            array(
                "type" => "text",
                "name" => $name
            ),
            $attributes
        );
        $input = $this->tag("input", null, $attributes, false);
        return $this->output($input);
    }
    /**
     *  Cria uma caixa de texto multi-linhas.
     *
     *  @param string $name Nome da caixa de texto
     *  @param string $value Conteudo da caixa de texto
     *  @param array $attr Atributos da tag
     *  @return string Caixa de texto do formulário
     */
    public function textarea($name = "", $attributes = array()) {
        $attributes = array_merge(
            array(
                "name" => $name
            ),
            $attributes
        );
        $input = $this->tag("textarea", array_unset($attributes, "value"), $attributes);
        return $this->output($input);
    }
    /**
     *  Cria uma caixa de texto para senhas.
     * 
     *  @param string $name Nome da caixa de texto
     *  @param string $value Conteudo da caixa de texto
     *  @param array $attr Atributos da tag
     *  @return string Caixa de texto do formulário
     */
    public function password($name = "", $attributes = array()) {
        $attributes = array_merge(
            array(
                "type" => "password",
                "name" => $name
            ),
            $attributes
        );
        $input = $this->tag("input", null, $attributes, false);
        return $this->output($input);
    }
    /**
     *  Cria uma caixa de texto para enviar arquivos.
     * 
     *  @param string $name Nome da caixa de envio de arquivo
     *  @param string $value Conteudo da caixa de envio de arquivo
     *  @return string Caixa de envio de arquivo do formulário
     */
    public function file($name = "", $attributes = array()) {
        $attributes = array_merge(
            array(
                "type" => "file",
                "name" => $name
            ),
            $attributes
        );
        $input = $this->tag("input", null, $attributes, false);
        return $this->output($input);
    }
    /**
     *  Cria um campo oculto.
     * 
     *  @param string $name Nome do campo oculto
     *  @param string $value Conteudo do campo oculto
     *  @param array $attr Atributos da tag
     *  @return string Campo oculto do formulário
     */
    public function hidden($name = "", $attributes = array()) {
        $attributes = array_merge(
            array(
                "type" => "hidden",
                "name" => $name
            ),
            $attributes
        );
        $input = $this->tag("input", null, $attributes, false);
        return $this->output($input);
    }
    /**
     *  Cria uma caixa de seleção.
     * 
     *  @param string $name Nome da caixa de seleção
     *  @param string $value Conteudo da caixa de seleção
     *  @param string $selected Opção selecionada por padrão
     *  @param array $attr Atributos da tag
     *  @return string Caixa de seleção do formulário
     */
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
    /**
     *  Cria caixa de entrada formatada e com label.
     * 
     *  @param string $name Nome do campo de entrada
     *  @param string $value Conteudo do campo de entrada
     *  @param array $attr Atributos da tag
     *  @return string Campo de entrada do formulário
     */
    public function input($name = "", $value = "", $options = array()) {
        $options = array_merge(array(
            "type" => "text",
            "label" => Inflector::humanize($name)
        ), $options);
        $type = array_unset($options, "type");
        $label = array_unset($options, "label");
        if($type == "select"):
            $values = $options["options"];
            unset($options["options"]);
            $input = $this->select($name, $values, $value, $options);
        else:
            $options["value"] = $value;
            $input = $this->{$type}($name, $options);
        endif;
        return $label != false ? $this->tag("label", "{$label}\n{$input}") : $input;
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