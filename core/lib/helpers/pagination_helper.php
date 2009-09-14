<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class PaginationHelper extends HtmlHelper {
    /**
     *  Short description.
     */
    public $model = false;

    /**
     *  Short description.
     *
     *  @param string $model
     *  @return object
     */
    public function model($model) {
        return $this->model = ClassRegistry::load($model);
    }
    /**
     *  Short description.
     *
     *  @param string $text
     *  @return string
     */
    public function next($text) {
        if($this->hasNext()):
            $page = $this->model->pagination["page"] + 1;
            return $this->link($text, $this->url($page));
        endif;
        return "";
    }
    /**
     *  Short description.
     *
     *  @param string $text
     *  @return string
     */
    public function previous($text) {
        if($this->hasPrevious()):
            $page = $this->model->pagination["page"] - 1;
            return $this->link($text, $this->url($page));
        endif;
        return "";
    }
    
    public function first($text) {
        if($this->hasPrevious()):
            $page = 1;
            return $this->link($text, $this->url($page));
        endif;
        return "";
    }
    public function last($text) {
        if($this->hasNext()):
            $page = $this->model->pagination["totalPages"];
            return $this->link($text, $this->url($page));
        endif;
        return "";
    }
    
    /**
     *  Short description.
     *
     *  @return boolean
     */
    public function hasNext() {
        if($this->model):
            return $this->model->pagination["page"] < $this->model->pagination["totalPages"];
        endif;
        return null;
    }
    /**
     *  Short description.
     *
     *  @return boolean
     */
    public function hasPrevious() {
        if($this->model):
            return $this->model->pagination["page"] != 1;
        endif;
        return null;
    }
    /**
     *  Short description.
     *
     *  @param integer $direction
     *  @return string
     */
    public function url($page) {
        return Mapper::url(array(
            "page" => $page
        ));
    }
}

?>