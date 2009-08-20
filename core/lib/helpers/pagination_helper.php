<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class PaginationHelper extends Helper {
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
    public function next($text = "&gt;") {
        $output = "";
        if($this->hasNext()):
        
        endif;
        return $this->output($output);
    }
    /**
     *  Short description.
     */
    public function previous($text = "&lt;") {
        $output = "";
        if($this->hasPrevious()):
            $output = $text;
        endif;
        return $this->output($output);
    }
    /**
     *  Short description.
     */
    public function hasNext() {
        return false;
    }
    /**
     *  Short description.
     */
    public function hasPrevious() {
        return $this->getPage() != 1;
    }
    /**
     *  Short description.
     */
    public function getPage() {
        if($this->model):
            return $this->model->getPage();
        endif;
        return false;
    }
}

?>