<?php

require 'lib/core/model/datasources/Datasource.php';

class Connection extends Object {
    protected $config = array();
    protected $connections = array();
    protected static $instance;

    public static function instance() {
        if(!isset(self::$instance)):
            $c = __CLASS__;
            self::$instance = new $c;
        endif;
        
        return self::$instance;
    }
    public static function add($name, $connection = null) {
        $self = self::instance();
        if(is_array($name)):
            $self->config += $name;
        else:
            $self->config[$name] = $connection;
        endif;
    }
    public static function getConfig($name) {
        $self = self::instance();
        return $self->config[$name];
    }
    public static function get($connection) {
        $self = self::instance();
        if(!array_key_exists($connection, $self->config)):
            trigger_error('Can\'t find database configuration. Check /config/connections.php', E_USER_ERROR);
            return false;
        endif;
        $config = $self->config[$connection];
        if(!array_key_exists($connection, $self->connections)):
            $self->connections[$connection] = self::create($config);
        endif;
        
        return $self->connections[$connection];
    }
    public static function create($config) {
        $datasource = $config['driver'] . 'Datasource';
        if(!class_exists($datasource)):
            require 'lib/core/model/datasources/' . $datasource . '.php';
        endif;
        
        return new $datasource($config);
    }
}