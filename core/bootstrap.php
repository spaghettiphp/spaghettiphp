<?php
/**
 *  Carregamento das funcionalidades básicas do Spaghetti.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

require_once CORE . DS . "basics.php";

/**
 *  Inclusão de todas as classes necessárias para a aplicação.
 */
App::import("Core", array("class_registry", "component", "connection", "controller", "datasource", "dispatcher", "helper", "inflector", "mapper", "model", "utils", "view"));

/**
 *  Inclusão dos arquivos de configuração da aplicação.
 */
App::import("App", array("config/settings", "config/routes", "config/database"));

/**
 *  Inclusão das classes da biblioteca do Spaghetti ou das classes as sobrescrevem;
 */
App::import("Controller", "app_controller");
App::import("Model", "app_model");

?>