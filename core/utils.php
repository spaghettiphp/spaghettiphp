<?php
/**
 *  Funções para us geral, na qual ajudam no desenvolvimento do seu projeto com
 *  o Spaghetti, facilitando na vizualização e comparação dos dados.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

/**
 *  Exibe os dados repassados para processamento visual.
 *
 *  @param array $data Dados a serem observados
 */
function pr($data) {
    echo "<pre>" . print_r($data, true) . "</pre>";
}
/**
 *  Retorna os dados enviados em forma de string para exibí-los no navegador.
 * 
 *  @param array $data Dados a serem observados
 */
function dump($data) {
    pr(var_export($data, true));
}
/**
 *  Analisa um texto, retornando o mesmo de acordo com a condição informada.
 * 
 *  @param string $condition Condição a ser verificada
 *  @param string $string Texto a ser analisado
 *  @return string Texto analisado para verdadeiro e nulo para falso
 */
function if_string($condition, $string) {
    if($condition && $condition != ""):
        return $string;
    endif;
    return "";
}
/**
 *  Verifica todos os argumentos repassados, retornando-os caso
 *  possuam um valor diferente de nulo.
 *
 *  @return string Variável analisada
 */
function pick() {
    $args = func_get_args();
    foreach($args as $arg):
        if($arg !== null):
            return $arg;
        endif;
    endforeach;
    return null;
}
/**
 *  Limpa o valor de um índice do array repassado.
 *
 *  @param array $array Array a ser utilizado
 *  @param string $index Índice a ser utilizado
 *  @return array Array limpo
 */
function array_unset(&$array = array(), $index = "") {
    $item = $array[$index];
    unset($array[$index]);
    return $item;
}
/**
 *  Verifica se um método é público para o objeto em questão.
 *
 *  @param object $object Objeto a ser analisado
 *  @param string $method Método a ser verificado
 *  @return boolean Verdadeiro para público
 */
function can_call_method(&$object, $method) {
    if(method_exists($object, $method)):
        $method = new ReflectionMethod($object, $method);
        return $method->isPublic();
    endif;
    return false;
}

?>