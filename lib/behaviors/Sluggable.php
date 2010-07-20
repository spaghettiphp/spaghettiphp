<?php

class Sluggable extends Behavior {
    public $filters = array(
        'beforeSave' => 'slug'
    );
    
    public function slug($data) {
        $data['slug'] = $this->getSlug($data['title']);
        return $data;
    }
    protected function getSlug($title, $number = null) {
        $slug = Inflector::slug(preg_replace("/[\d]+/", "", $title));
        // if(is_null($number)):
        //     $number = 1;
        // else:
        //     $slug = $slug . "-" . $number;
        // endif;
        // 
        // return $this->isUnique($slug, "slug") ? $slug : $this->getSlug($title, ++$number);
        return $slug;
    }
}