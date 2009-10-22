<?php
/**
 *  Controle das sessões do Spachetti.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class Session extends Object {
    /**
     *  Inicializa as sessões para gerenciamento.
     *
     *  @return boolean Verdadeiro para sessão criada
     */
    public static function start() {
        return session_start();
    }
    /**
     *  Verifica se a sessão foi criada com sucesso.
     *
     *  @return boolean Verdadeiro para sessão criada
     */
    public static function started() {
        return isset($_SESSION);
    }
    /**
     *  Lê uma variável setada pela sessão.
     * 
     *  @param string $name Variável a ser retornada
     *  @return string Valor da variável solicitada
     */
    public static function read($name) {
        if(!self::started()) self::start();
        return $_SESSION[$name];
    }
    /**
     *  Escreve uma variável com seu respectivo valor na sessão.
     *
     *  @param string $name Valor da variável
     *  @param string $value Conteudo da variável
     */
    public static function write($name, $value) {
        if(!self::started()) self::start();
        $_SESSION[$name] = $value;
    }
    /**
     *  Remove uma variável setada na sessão.
     *
     *  @param string $name Variável a ser removida
     *  @return boolean Verdadeiro para remoção da variável
     */
    public static function delete($name) {
        if(!self::started()) self::start();
        unset($_SESSION[$name]);
        return true;
    }
}

?>