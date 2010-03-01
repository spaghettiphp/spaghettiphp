<?php
/**
 *  Esse é o arquivo de entrada para todas as requisições do Spaghetti*. A partir
 *  daqui todos os arquivos necessários são carregados e a mágica começa.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

require dirname(dirname(dirname(__FILE__))) . '/config/bootstrap.php';

$dispatcher = new Dispatcher;
$dispatcher->dispatch();