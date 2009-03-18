<?php
/**
 *  Helper é uma classe herdada por todos os helpers do Spaghetti, definindo apenas
 *  os métodos essenciais para seu funcionamento.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

abstract class Helper extends Object {
    /**
     *  Retorna conteúdo para ser usado na saída de informações.
     *
     *  @param string $out Informação a ser retornada
     *  @return string Informação retornada
     */
    public function output($out = "") {
        return $out;
    }
}

?>