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
     */
    public function model($model) {
        if($this->model = ClassRegistry::load($model)):
            return true;
        endif;
        return false;
    }
    /**
     *  Short description.
     */
    public function next($text) {
        if($this->hasNext()):
            return $this->link($text, $this->getUrl(1));
        endif;
        return "";
    }
    /**
     *  Short description.
     */
    public function previous($text) {
        if($this->hasPrevious()):
            return $this->link($text, $this->getUrl(-1));
        endif;
        return "";
    }
    /**
     *  Short description.
     */
    public function hasNext() {
        if($this->model):
            return $this->model->pagination["page"] < $this->model->pagination["totalPages"];
        endif;
        return false;
    }
    /**
     *  Short description.
     */
    public function hasPrevious() {
        if($this->model):
            return $this->model->pagination["page"] != 1;
        endif;
        return false;
    }
    public function getUrl($direction) {
        $page = $this->model->pagination["page"] + $direction;
        $here = Mapper::here();
        if(preg_match("/page:\d?/", $here)):
            $url = preg_replace("%page:\d+?%", "page:{$page}", $here);
        else:
            $url = "{$here}/page:{$page}";
        endif;
        return $url;
    }
}

?>