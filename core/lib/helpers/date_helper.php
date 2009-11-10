<?php
/**
 *  DateHelper provê funções de formatação de data.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class DateHelper extends Helper {
    /**
      *  Formata uma data.
      *
      *  @param string $format Formato de data
      *  @param string $date Data compatível com strtotime
      *  @return string Data formatada
      */
    public function format($format, $date) {
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }
}

?>