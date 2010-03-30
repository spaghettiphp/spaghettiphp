<?php

require 'lib/core/model/datasources/Datasource.php';

class Connection extends Object {
    protected $config = array();
    protected $datasources = array();
    public static $instance;

    public static function &getInstance() {
        if(!isset(self::$instance)):
            $c = __CLASS__;
            self::$instance = new $c;
        endif;
        return self::$instance;
    }
    public static function add($name, $connection = null) {
        $self = self::getInstance();
        if(is_array($name)):
            $self->config += $name;
        else:
            $self->config[$name] = $connection;
        endif;
    }
    public static function &getDatasource($connection = null) {
        $self = self::getInstance();
        $connection = is_null($connection) ? Config::read('App.environment') : $connection;
        if(!array_key_exists($connection, $self->config)):
            trigger_error('Can\'t find database configuration. Check /config/connections.php', E_USER_ERROR);
            return false;
        endif;
        $config = $self->config[$connection];
        $datasource = $config['driver'] . 'Datasource';
        if(!array_key_exists($connection, $self->datasources)):
            if(self::loadDatasource($datasource)):
                $self->datasources[$connection] = new $datasource($config);
            else:
                trigger_error('Can\'t find ' . $datasource . ' datasource', E_USER_ERROR);
                return false;
            endif;
        endif;
        
        return $self->datasources[$connection];
    }
    public static function loadDatasource($datasource) {
        if(!class_exists($datasource)):
            require 'lib/core/model/datasources/' . $datasource . '.php';
        endif;
        return true;
   }
}