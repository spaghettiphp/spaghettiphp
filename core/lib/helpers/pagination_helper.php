<?php
/**
 *  Geração automática dos elementos HTML para uso com a paginação.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class PaginationHelper extends HtmlHelper {
    /**
     *  Model a ser utilizado na paginação.
     */
    public $model = false;

    /**
     *  Carrega o Model a ser utilizado na paginação.
     *
     *  @param string $model Model utilizado
     *  @return object
     */
    public function model($model) {
        return $this->model = ClassRegistry::load($model);
    }
    /**
     *  Gera o link para a página seguinte de acordo com os dados encontrados.
     *
     *  @param string $text Texto a ser expresso no link
     *  @param array $attr Atributos extras para o link
     *  @return string Link para a página seguinte
     */
    public function next($text = 'Próximo >>', $attr = array()) {
        if($this->hasNext()):
            $page = $this->model->pagination["page"] + 1;
            return $this->link($text, $this->url($page), $attr);
        endif;
        return "";
    }
    /**
     *  Gera o link para a página anterior de acordo com os dados encontrados.
     *
     *  @param string $text Texto a ser expresso no link
     *  @param array $attr Atributos extras para o link
     *  @return string Link para a página anterior
     */
    public function previous($text = '<< Anterior', $attr = array()) {
        if($this->hasPrevious()):
            $page = $this->model->pagination["page"] - 1;
            return $this->link($text, $this->url($page), $attr);
        endif;
        return "";
    }
    /**
     *  Gera o link para a página inicial de acordo com os dados encontrados.
     *
     *  @param string $text Texto a ser expresso no link
     *  @param array $attr Atributos extras para o link
     *  @return string Link para a página inicial
     */
    public function first($text = 'Início', $attr = array()) {
        if($this->hasPrevious()):
            $page = 1;
            return $this->link($text, $this->url($page), $attr);
        endif;
        return "";
    }
    /**
     *  Gera o link para a página final de acordo com os dados encontrados.
     *
     *  @param string $text Texto a ser expresso no link
     *  @param array $attr Atributos extras para o link
     *  @return string Link para a página final
     */
    public function last($text = 'Fim', $attr = array()) {
        if($this->hasNext()):
            $page = $this->model->pagination["totalPages"];
            return $this->link($text, $this->url($page), $attr);
        endif;
        return "";
    }
    
    /**
     *  Verifica a existência da página seguinte caso não esteja na última página.
     *
     *  @return boolean Verdadeiro caso exista uma próxima página
     */
    public function hasNext() {
        if($this->model):
            return $this->model->pagination["page"] < $this->model->pagination["totalPages"];
        endif;
        return null;
    }
    /**
     *  Verifica a existência da página anterior caso não esteja na primeira página.
     *
     *  @return boolean Verdadeiro caso exista uma página anterior
     */
    public function hasPrevious() {
        if($this->model):
            return $this->model->pagination["page"] != 1;
        endif;
        return null;
    }
    /**
     *  Gera URL de acordo com a página atual, inserindo os dados da paginação.
     *
     *  @param string $page Página visualizada
     *  @return string URL contendo a página visualizada
     */
    public function url($page) {
        return Mapper::url(array(
            "page" => $page
        ));
    }
}

?>