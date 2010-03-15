<?php
/**
 *  Funções para uso geral, que ajudam no desenvolvimento do seu projeto com
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
 *  @return void
 */
function pr($data) {
    echo "<pre>" . print_r($data, true) . "</pre>";
}

/**
 *  Retorna os dados enviados em forma de string para exibí-los no navegador.
 * 
 *  @param array $data Dados a serem observados
 *  @return void
 */
function dump($data) {
    pr(var_export($data, true));
}

/**
 *  Limpa o valor de um índice do array repassado, retornando-o.
 *
 *  @param array $array Array a ser utilizado
 *  @param string $index Índice a ser utilizado
 *  @return array Item removido
 */
function array_unset(&$array = array(), $index) {
    if(array_key_exists($index, $array)):
        $item = $array[$index];
        unset($array[$index]);
        return $item;
    endif;
    return null;
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

/**
  *  Cria um array preenchido com uma sequência.
  *
  *  @param integer $min Valor mínimo
  *  @param integer $max Valor máximo
  *  @return array Sequência
  */
function array_range($min, $max) {
    $result = array();
    for($i = $min; $i < $max + 1; $i++):
        $result[$i] = $i;
    endfor;
    return $result;
}

?>