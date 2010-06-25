<?php

class PaginationHelper extends Helper {
    protected $model;

    public function model($model) {
        $this->model = Loader::instance("Model", $model);
        return $this;
    }
    public function numbers($options = array()) {
        if(!$this->model):
            return null;
        endif;
        $options += array(
            'modulus' => 3,
            'separator' => ' ',
            'tag' => 'span',
            'current' => 'current'
        );
        $page = $this->page();
        $pages = $this->pages();
        $numbers = array();
        $start = max($page - $options['modulus'], 1);
        $end = min($page + $options['modulus'], $pages);

        for($i =$start; $i <= $end; $i++):
            if($i == $page):
                $attributes = array('class' => $options['current']);
                $number = $i;
            else:
                $attributes = array();
                $number = $this->html->link($i, array('page' => $i));
            endif;
            $numbers []= $this->html->tag($options['tag'], $number, $attributes);
        endfor;

        return join($options['separator'], $numbers);
    }
    public function next($text, $attr = array()) {
        if($this->hasNext()):
            $page = $this->page() + 1;
            return $this->html->link($text, array('page' => $page), $attr);
        endif;
        
        return '';
    }
    public function previous($text, $attr = array()) {
        if($this->hasPrevious()):
            $page = $this->page() - 1;
            return $this->html->link($text, array('page' => $page), $attr);
        endif;
        
        return '';
    }
    public function first($text, $attr = array()) {
        if($this->hasPrevious()):
            return $this->html->link($text, array('page' => 1), $attr);
        endif;
        
        return '';
    }
    public function last($text, $attr = array()) {
        if($this->hasNext()):
            $page = $this->model->pagination['totalPages'];
            return $this->html->link($text, array('page' => $page), $attr);
        endif;
        
        return '';
    }
    public function hasNext() {
        if($this->model):
            return $this->page() < $this->pages();
        endif;
        
        return null;
    }
    public function hasPrevious() {
        if($this->model):
            return $this->page() > 1;
        endif;
        
        return null;
    }
    public function page() {
        if($this->model):
            return $this->model->pagination['page'];
        endif;
        
        return null;
    }
    public function pages() {
        if($this->model):
            return $this->model->pagination['totalPages'];
        endif;
        
        return null;
    }
    public function records() {
        if($this->model):
            return $this->model->pagination['totalRecords'];
        endif;
        
        return null;
    }
}