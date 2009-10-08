<?php

class UsersRoles extends AppModel {
    public $belongsTo = array(
        "Roles" => array("foreignKey" => "role_id")
    );
}

?>