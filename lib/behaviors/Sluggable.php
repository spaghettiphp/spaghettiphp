<?php

class Sluggable extends Behavior {
    protected $filters = array(
        'beforeSave' => 'slug'
    );
    
    public function slug($data) {
        $data['slug'] = $this->getSlug($data['title']);
        return $data;
    }
    protected function getSlug($title, $number = null) {
        $slug = Inflector::slug($title);
        if(is_null($number)):
            $number = 1;
        else:
            $slug = $slug . "-" . $number;
        endif;
        
        if($this->model->exists(array('slug' => $slug))):
            return $this->getSlug($title, $number + 1);
        else:
            return $slug;
        endif;
    }
}