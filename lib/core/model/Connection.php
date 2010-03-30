<?php

class Connection extends Object {
    private $config = array();
    private $datasources = array();

    public function __construct() {
        $this->config = Config::read('database');
    }
    public static function &getInstance() {
        static $instance = array();
        if(!isset($instance[0]) || !$instance[0]):
            $instance[0] = new Connection;
        endif;
        return $instance[0];
    }
    
    public static function add($name, $connection = null) {
        
    }
    
    public static function &getDatasource($environment = null) {
        $self = self::getInstance();
        $environment = is_null($environment) ? Config::read('App.environment') : $environment;
        if(isset($self->config[$environment])):
            $config = $self->config[$environment];
        else:
            trigger_error('Can\'t find database configuration. Check /app/config/database.php', E_USER_ERROR);
            return false;
        endif;
        $datasource = Inflector::camelize($config['driver'] . '_datasource');
        if(isset($self->datasources[$environment])):
            return $self->datasources[$environment];
        elseif(self::loadDatasource($datasource)):
            $self->datasources[$environment] = new $datasource($config);
            return $self->datasources[$environment];
        else:
            trigger_error('Can\'t find ' . $datasource . ' datasource', E_USER_ERROR);
            return false;
        endif;
    }
    public static function loadDatasource($datasource) {
        if(!class_exists($datasource)):
            require 'lib/core/model/datasources/' . $datasource . '.php';
        endif;
        return true;
   }
}