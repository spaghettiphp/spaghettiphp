<?php
/**
 *  Short description.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Sanitize {
    /**
      *  Short description.
      *
      *  @param string $string
      *  @param boolean $encode
      *  @return string
      */
    public static function html($string, $encode = true) {
        $filter = $encode ? FILTER_SANITIZE_SPECIAL_CHARS : FILTER_SANITIZE_STRING;
        return filter_var($string, $filter, FILTER_FLAG_ENCODE_AMP);
    }
}

?>