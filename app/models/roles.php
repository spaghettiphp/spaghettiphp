<?php

class Roles extends AppModel {
    public $hasMany = array("UsersRoles" => array("foreignKey" => "role_id"));
}

?>