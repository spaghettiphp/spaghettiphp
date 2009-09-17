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
     *  @param boolean $empty Verdadeiro para criar uma tag vazia
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
        if(!$this->external($src)):
            $src = Mapper::url("/images/" . $src, $full);
        endif;
        $attr["src"] = $src;
        return $this->output($this->tag("img", null, $attr, true));
    }
    /**
     *  Cria elementos de folha de estilho para serem usados no HTML.
     * 
     *  @param string $href Caminho da folha de estilo a ser inserida no HTML
     *  @param array $attr Atributos da tag
     *  @param boolean $inline Verdadeiro para imprimir a folha de estilo inline
     *  @param boolean $full Verdadeiro para gerar uma URL completa
     *  @return string Elemento de folha de estilo a ser utilizado
     */
    public function stylesheet($href = "", $attr = array(), $inline = true, $full = false) {
        if(is_array($href)):
            $tags = "";
            foreach($href as $tag):
                $tags .= $this->stylesheet($tag, $attr, $full) . PHP_EOL;
            endforeach;
            return $tags;
        endif;
        if(!$this->external($href)):
            $href = "/styles/" . $this->extension($href, "css");
        endif;
        $attr = array_merge(
            array(
                "href" => Mapper::url($href, $full),
                "rel" => "stylesheet",
                "type" => "text/css"
            ),
            $attr
        );
        return $this->output($this->tag("link", null, $attr, true));
    }
    /**
     *  Cria um elemento de script para ser usado no HTML.
     * 
     *  @param string $src Caminho do script a ser inseido no HTML
     *  @param array $attr Atributos da tag
     *  @param boolean $inline Verdadeiro para imprimir o script inline
     *  @param boolean $full Verdadeiro para gerar uma URL completa
     *  @return string Elemento de script a ser utilizado
     */
    public function script($src = "", $attr = array(), $inline = true, $full = false) {
        if(is_array($src)):
            $tags = "";
            foreach($src as $tag):
                $tags .= $this->script($tag, $attr, $full) . PHP_EOL;
            endforeach;
            return $tags;
        endif;
        if(!$this->external($src)):
            $src = "/scripts/" . $this->extension($src, "js");
        endif;
        $attr = array_merge(
            array(
                "src" => $src,
                "type" => "text/javascript"
            ),
            $attr
        );
        return $this->output($this->tag("script", null, $attr));
    }
    /*
     *  Cria uma lista a partir de um array.
     *  
     *  @param array $list Array com conjunto de elementos da lista
     *  @return string
     */
    public function nestedList($list, $attr = array(), $type = "ul") {
        $content = "";
        foreach($list as $k => $li):
            if(is_array($li)):
                $li = $this->nestedList($li, array(), $type);
                if(!is_numeric($k)):
                    $li = $k . $li;
                endif;
            endif;
            $content .= $this->tag("li", $li) . PHP_EOL;
        endforeach;
        return $this->tag($type, $content, $attr);
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
     *  Adiciona uma meta tag para definir o charset da página.
     *
     *  @param string $charset Charset a ser utilizado
     *  @return string Tag META
     */
    public function charset($charset = null) {
        if(is_null($charset)):
            $charset = Config::read("appEncoding");
        endif;
        $attr = array(
            "http-equiv" => "Content-type",
            "content" => "text/html; charset={$charset}"
        );
        return $this->output($this->tag("meta", null, $attr));
    }
    /**
     *  Verifica se uma URL é externa.
     *
     *  @param string $url URL a ser verificada
     *  @return boolean Verdadeiro se a URL for externa
     */
    public function external($url) {
        return preg_match("/^[a-z]+:/", $url);
    }
    /**
     *  Adiciona uma extensão a um arquivo caso ela não exista.
     *
     *  @param string $file Nome do arquivo
     *  @param string $extension Extensão a ser adicionada
     *  @return string Novo nome do arquivo
     */
    public function extension($file, $extension) {
        if(strpos($file, "?") === false):
            if(strpos($file, "." . $extension) === false):
                $file .= "." . $extension;
            endif;
        endif;
        return $file;
    }
}

?>