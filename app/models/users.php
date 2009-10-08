<?php

class Users extends AppModel {
    public $hasMany = array("UsersRoles" => array("foreignKey" => "user_id"));
}

?>