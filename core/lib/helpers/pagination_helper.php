<?php
/**
 *  Geração automática dos elementos HTML para uso com a paginação.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

App::import("Helper", "html_helper");

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
     *  Short description.
     *
     *  @param array $options
     *  @return string
     */
    public function numbers($options = array()) {
        $options = array_merge(
            array(
                "modulus" => 3,
                "separator" => null,
                "tag" => "span"
            ),
            $options
        );
        $page = $this->model->pagination["page"];
        $pages = $this->model->pagination["totalPages"];
        $numbers = array();
        for($i = $page - $options["modulus"]; $i <= $page + $options["modulus"]; $i++):
            if($i > 0 && $i <= $pages):
                if($i != $page):
                    $number = $this->link($i, array("page" => $i));
                else:
                    $number = $i;
                endif;
                $numbers []= $this->tag($options["tag"], $number);
            endif;
        endfor;
        return join($options["separator"], $numbers);
    }
    /**
     *  Gera o link para a página seguinte de acordo com os dados encontrados.
     *
     *  @param string $text Texto a ser expresso no link
     *  @param array $attr Atributos extras para o link
     *  @return string Link para a página seguinte
     */
    public function next($text, $attr = array()) {
        if($this->hasNext()):
            $page = $this->model->pagination["page"] + 1;
            return $this->link($text, array("page" => $page), $attr);
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
    public function previous($text, $attr = array()) {
        if($this->hasPrevious()):
            $page = $this->model->pagination["page"] - 1;
            return $this->link($text, array("page" => $page), $attr);
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
    public function first($text, $attr = array()) {
        if($this->hasPrevious()):
            $page = 1;
            return $this->link($text, array("page" => $page), $attr);
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
    public function last($text, $attr = array()) {
        if($this->hasNext()):
            $page = $this->model->pagination["totalPages"];
            return $this->link($text, array("page" => $page), $attr);
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
}

?>