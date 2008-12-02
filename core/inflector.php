<?php
/**
 *  A classe Inflector é responsável pelas conversões de strings como remoção de
 *  acentos e caracteres especiais, camelização, humanização, entre outros. E em
 *  breve abrigará também as funções de pluralização e singularização de nomes.
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Inflector
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */

class Inflector extends Object {
    /**
     * Este método transforma uma string para o formato camelizado.
     * Ex.: a-casa-amarela => aCasaAmarela
     *
     * @param string $string String de entrada
     * @return string String de saída
     */
    public function camelize($string = "") {
        return str_replace(" ", "", ucwords(str_replace(array("_", "-"), " ", $string)));
    }
    /**
     * Este método transforma uma string para o formato humanizado.
     * Ex.: a-casa-amarela => A Casa Amarela
     *
     * @param string $string String de entrada
     * @return string String de saída
     */
    public function humanize($string = "") {
        return ucwords(str_replace(array("_", "-"), " ", $string));
    }
    /**
     * Este método remove espaços de uma string e converte as letras para caixa-baixa.
     * Ex.: A Casa Amarela => a-casa-amarela
     *
     * @param string $string String de entrada
     * @return strign String de saída
     */
    public function underscore($string = "") {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
    }
    /**
     * O método Inflector::slug() transforma uma string no formato slug, em caixa-baixa,
     * com espaços substituídos por hífens, com a remocao de caracteres acentuados e especiais,
     * deixando apenas letras minúsculas.
     *
     * @param string $string String de entrada
     * @param string $replace String para substituição do espaço
     * @return string String de saída
     */
    public function slug($string = "", $replace = "-") {
        $map = array(
            "/à|á|å|â|ã/" => "a",
            "/è|é|ê|ẽ|ë/" => "e",
            "/ì|í|î/" => "i",
            "/ò|ó|ô|ø|õ/" => "o",
            "/ù|ú|ů|û/" => "u",
            "/ç/" => "c",
            "/ñ/" => "n",
            "/ä|æ/" => "ae",
            "/ö/" => "oe",
            "/ü/" => "ue",
            "/Ä/" => "Ae",
            "/Ü/" => "Ue",
            "/Ö/" => "Oe",
            "/ß/" => "ss",
            "/[^\w\s]/" => " ",
            "/\\s+/" => $replace,
            "/^{$replace}+|{$replace}+$/" => ""
        );
        return strtolower(preg_replace(array_keys($map), array_values($map), $string));
    }
}
?>