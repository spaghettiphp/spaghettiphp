<?php

class Sanitize {
    public static function html($string, $encode = true) {
        $filter = $encode ? FILTER_SANITIZE_SPECIAL_CHARS : FILTER_SANITIZE_STRING;
        return filter_var($string, $filter, FILTER_FLAG_ENCODE_AMP);
    }
}