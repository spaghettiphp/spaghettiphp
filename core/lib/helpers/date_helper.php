<?php
/**
 *  Short description.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class DateHelper extends Helper {
    public function format($format, $date) {
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }
}

?>