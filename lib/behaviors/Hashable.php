<?php

class Hashable extends Behavior {
    public $filters = array(
        'beforeSave' => 'hash'
    );
    
    public function hash($data) {
        $password = array_unset($data, "password");
        if(!empty($password)):
            $data["password"] = Security::hash($password, "sha1", true);
        endif;
        return $data;
    }
}