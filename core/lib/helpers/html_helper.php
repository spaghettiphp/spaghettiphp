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
     *  Cria um link para ser utilizado na aplicação.
     * 
     *  @param string $text Conteúdo para o link
     *  @param string $url URL relativa à raiz da aplicação
     *  @param array $attr Atributos da tag
     *  @param boolean $full Verdadeiro para gerar uma URL completa
     *  @return string Link HTML
     */
    public function link($text, $url = null, $attr = array(), $full = false) {
        if(is_null($url)):
            $url = $text;
        endif;
        $attr["href"] = Mapper::url($url, $full);
        return $this->output($this->tag("a", $text, $attr));
    }
    /**
     *  Cria um elemento de imagem para ser na aplicação.
     * 
     *  @param string $src Caminho da imagem
     *  @param array $attr Atributos da tag
     *  @param boolean $full Verdadeiro para gerar uma URL completa
     *  @return string HTML da imagem a ser inserida
     */
    public function image($src, $attr = array(), $full = false) {
        $src = $this->internalUrl("/images", $src, $full);
        $attr["src"] = $src;
        return $this->output($this->tag("img", null, $attr, true));
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
     *  Cria uma tag DIV.
     *
     *  @param string $content Conteúdo da tag
     *  @param array $attr Atributos da tag
     *  @return string Tag DIV
     */
    public function div($content, $attr = array()) {
        if(!is_array($attr)):
            $attr = array("class" => $attr);
        endif;
        return $this->output($this->tag("div", $content, $attr));
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