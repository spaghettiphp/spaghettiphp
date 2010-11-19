<?php

class Sluggable extends Behavior {
    protected $filters = array(
        'beforeSave' => 'slug'
    );
    protected $defaults = array('title' => 'slug');
    
    public function slug($data) {
        foreach($this->options as $title => $slug) {
            if($this->shouldUpdateSlug($data, $title, $slug)) {
                $data[$slug] = $this->getSlug($slug, $data[$title]);
            }
        }

        return $data;
    }
    
    protected function shouldUpdateSlug($data, $title, $slug) {
        return (
            is_null($this->model->id) &&
            array_key_exists($title, $data) &&
            !empty($data[$title])
        );
    }
    
    protected function getSlug($field, $title) {
        $slug = $start = Inflector::slug($title);
        $number = 1;

        while($this->model->exists(array(
            $field => $slug
        ))) {
            $slug = $start . '-' . ($number += 1);
        }
        
        return $slug;
    }
    
    protected function options($options) {
        return empty($options) ? $this->defaults : $options;
    }
}