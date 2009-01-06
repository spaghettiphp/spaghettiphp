<?php
/**
 *  Configurações necessárias para rodar testes através do SimpleTest
 *
 *  Licensed under The MIT License.
 *  Redistributions of files must retain the above copyright notice.
 *  
 *  @package Spaghetti
 *  @subpackage Spaghetti.Core.Tests.Setup
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */


/**
 * Define o caminho do SimpleTest
 */
if (! defined('SIMPLE_TEST')):
    define('SIMPLE_TEST', '../../../simpletest/');
endif;
/**
 * Inclui o auto-run do SimpleTest
 */
require_once(SIMPLE_TEST . 'autorun.php');

/**
 * Inclui as dependências necessárias do Spaghetti
 */
require_once "../../spaghetti.php";

?>
