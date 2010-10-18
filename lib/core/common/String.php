<?php

class String {
    public static $start = ':';
    public static $end = '';

    public static function insert($string, $data) {
        asort($data);
        foreach($data as $key => $value):
            $regex = '%(' . self::$start . $key . self::$end . ')%';
            $string = preg_replace($regex, $value, $string);
        endforeach;
        
        return $string;
    }
    public static function extract($string) {
        preg_match_all('%:([a-zA-Z-_]+)%', $string, $extracted);
        
        return $extracted[1];
    }
}