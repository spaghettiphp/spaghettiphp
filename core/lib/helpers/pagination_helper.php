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
            return $this->link($text, $this->url(1));
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
            return $this->link($text, $this->url(-1));
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
    public function url($direction) {
        return Mapper::url(array(
            "page" => $this->model->pagination["page"] + $direction
        ));
    }
}

?>