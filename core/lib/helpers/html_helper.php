<?php
/**
 *  Geração automática dos elementos HTML de acordo com os dados passados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class HtmlHelper extends Helper {
    /**
     *  Cria as tags (únicas) HTML fechando-as se necessário.
     *
     *  @param string $tag Tag a ser criada
     *  @param string $attr Atributos e opções da tag HTML
     *  @param boolean $close Fecha as tags (true)
     *  @return string Tag HTML
     */
    public function openTag($tag = "", $attr = "", $close = true) {
        $html = "<{$tag}";
        if(($attr = $this->attr($attr)) != ""):
            $html .= " $attr";
        endif;
        $html .= ($close ? "" : " /") . ">";
        return $html;
    }
    /**
     *  Fecha as tags HMTL.
     * 
     *  @param string $tag Tag a ser fechado
     *  @return string Tag HMTL fechado
     */
    public function closeTag($tag = "") {
        return "</{$tag}>";
    }
    /**
     *  Cria as tags (pares) HTML com o seu conteudo, fechando-o caso necessário.
     * 
     *  @param string $tag Tag HTML para inserção
     *  @param string $content Conteúdo entre as tags inseridos
     *  @param array $attr Atributos e opções da tag HTML
     *  @param boolean $close Verdadero para fechar a tag em questão
     *  @return string Tag HTML com o seu conteudo
     */
    public function tag($tag = "", $content = "", $attr = array(), $close = true) {
        $html = $this->openTag($tag, $attr, $close);
        if($close):
            $html .= "{$content}" . $this->closeTag($tag);
        endif;
        return $html;
    }
    /**
     *  Prepara os atributos para utilização nas tags.
     * 
     *  @param array $attr Atributos e opções da tag HTML
     *  @return string Atributos para pre-enchimento da tag
     */
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
    /**
     *  Cria links par serem utilizados na aplicação.
     * 
     *  @param string $text Conteúdo para o link
     *  @param string $url URL relativa a instalação
     *  @param array $attr Atributos e opções da tag HTML
     *  @param boolean $full URL completa (true) ou apenas o caminho
     *  @return string Link em HTML gerado para a aplicação
     */
    public function link($text = "", $url = "", $attr = array(), $full = false) {
        if(!is_array($attr)):
            $attr = array();
        endif;
        $href = array("href" => Mapper::url($url, $full));
        $attr = array_merge($href, $attr);
        return $this->output($this->tag("a", $text, $attr));
    }
    /**
     *  Cria o elemento imagem para ser usado no HTML.
     * 
     *  @param string $src Nome da imagem a ser inserido no HTML
     *  @param string $alt Texto alternativo da imagem
     *  @param array $attr Atributos e opções da tag HTML
     *  @param boolean $full URL completa (true) ou apenas o caminho
     *  @return string ML da imagem a ser inserida
     */
    public function image($src = "", $alt = "", $attr = array(), $full = false) {
        if(!is_array($attr)):
            $attr = array();
        endif;
        $src_alt = array("src" => $this->internalUrl("/images", $src, $full), "alt" => $alt);
        $attr = array_merge($src_alt, $attr);
        return $this->output($this->tag("img", null, $attr, false));
    }
    /**
     *  Cria o elemento folha de estilho para ser usado no HTML.
     * 
     *  @param string $href Nome da folha de estilo a ser inserido no HTML
     *  @param array $attr Atributos e opções da tag HTML
     *  @param boolean $full URL completa (true) ou apenas o caminho
     *  @return string URL da folha de estilo a ser utilizada
     */
    public function stylesheet($href = "", $attr = array(), $full = false) {
        $tags = "";
        if(is_array($href)):
            foreach($href as $tag):
                $tags .= HtmlHelper::stylesheet($tag, $attr, $full) . PHP_EOL;
            endforeach;
            return $tags;
        endif;
        $attrs = array("href" => $this->internalUrl("/styles", $href, $full), "rel" => "stylesheet", "type" => "text/css");
        $attr = array_merge($attrs, $attr);
        return $this->output($this->tag("link", null, $attr, false));
    }
    /**
     *  Cria o elemento script para ser usado no HTML.
     * 
     *  @param string $src Nome do script a ser inseido no HTML
     *  @param array $attr Atributos e opções da tag HTML
     *  @param boolean $full URL completa (true) ou apenas o caminho
     *  @return string URL do script a ser utilizado
     */
    public function script($src = "", $attr = array(), $full = false) {
        $tags = "";
        if(is_array($src)):
            foreach($src as $tag):
                $tags .= HtmlHelper::script($tag, $attr, $full) . PHP_EOL;
            endforeach;
            return $tags;
        endif;
        $attrs = array("src" => $this->internalUrl("/scripts", $src, $full), "type" => "text/javascript");
        $attr = array_merge($attrs, $attr);
        return $this->output($this->tag("script", null, $attr));
    }
    /**
     *  Short description.
     *
     *  @return string
     */
    public function div($content, $attributes = array()) {
        if(!is_array($attributes)):
            $attributes = array("class" => $attributes);
        endif;
        return $this->output($this->tag("div", $content, $attributes));
    }
    /**
     *  Cria uma URL interna para utilização no HTML.
     * 
     *  @param string $path Caminho relativo a URL
     *  @param string $url URL a ser inserido
     *  @param boolean $full URL completa (true) ou apenas o caminho
     *  @return string URL interna a ser utilizada
     */
    public function internalUrl($path = "", $url = "", $full = false) {
        if(preg_match("/^[a-z]+:/", $url)):
            return $url;
        else:
            return Mapper::url("{$path}/{$url}", $full);
        endif;
    }
}

?>