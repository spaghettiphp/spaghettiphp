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
     *  Cria HTML para tags de abertura.
     *
     *  @param string $tag Tag a ser criada
     *  @param string $attr Atributos da tag
     *  @param boolean $empty Verdadeiro para criar uma tag vazia
     *  @return string Tag HTML
     */
    public function openTag($tag, $attr = array(), $empty = false) {
        $html = "<{$tag}";
        $attr = $this->attr($attr);
        if(!empty($attr)):
            $html .= " $attr";
        endif;
        $html .= ($empty ? " /" : "") . ">";
        return $html;
    }
    /**
     *  Cria HTML para tag de fechamento.
     * 
     *  @param string $tag Tag a ser fechada
     *  @return string Tag HMTL fechada
     */
    public function closeTag($tag) {
        return "</{$tag}>";
    }
    /**
     *  Cria HTML para tags de abertura e fechamento contendo algum conteúdo.
     * 
     *  @param string $tag Tag a ser criada
     *  @param string $content Conteúdo entre as tags inseridas
     *  @param array $attr Atributos da tag
     *  @param boolean $close Verdadeiro para criar uma tag vazia
     *  @return string Tag HTML com o seu conteúdo
     */
    public function tag($tag, $content = "", $attr = array(), $empty = false) {
        $html = $this->openTag($tag, $attr, $empty);
        if(!$empty):
            $html .= "{$content}" . $this->closeTag($tag);
        endif;
        return $html;
    }
    /**
     *  Prepara atributos para utilização em tags HTML.
     * 
     *  @param array $attr Atributos a serem preparados
     *  @return string Atributos para preenchimento da tag
     */
    public function attr($attr) {
        $attributes = array();
        foreach($attr as $name => $value):
            if($value === true):
                $value = $name;
            elseif($value === false):
                continue;
            endif;
            $attributes []= $name . '="' . $value . '"';
        endforeach;
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