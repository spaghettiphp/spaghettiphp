<?php
/**
 *  Definições e inclusões necessários para o funcionamento do Spaghetti.
 *  
 *  Spaghetti is licensed under the MIT License. By using this software, you agree
 *  with the terms specified below. The license agreement extends to all the files
 *  within this installation.
 *
 *  The MIT License
 *  
 *  Copyright (c) 2008 Julio Greff de Oliveira,
 *                     Rafael Marin Bortolotto.
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 *
 *  @package Spaghetti
 *  @license http://www.opensource.org/licenses/mit-license.php The MIT License
 *
 */

    /**
     * Definição dos diretórios do Spaghetti. Você pode editar esses diretórios
     * caso esteja usando o Spaghetti com uma estrutura de diretórios diferente
     * da padrão.
     */
    $self = dirname($_SERVER["PHP_SELF"]);
    while(in_array(basename($self), array("app", "core", "tests", "webroot"))):
        $self = dirname($self);
    endwhile;

    define("DS", DIRECTORY_SEPARATOR);
    define("ROOT", dirname(__FILE__));
    define("BASE_URL", "http://" . $_SERVER["HTTP_HOST"]);
    define("CORE", ROOT . DS . "core");
    define("LIB", CORE . DS . "lib");
    define("APP", ROOT . DS . "app");
    define("WEBROOT", $self);

    /**
     * Inclusão das classes básicas do Spaghetti.
     */
    require_once CORE . DS . "basics.php";
    App::import("Core", array("class_registry", "component", "controller", "dispatcher", "filter", "helper", "inflector", "mapper", "misc", "model", "view"));
    /**
     * Inclusão de configurações e classes básicas, seja da biblioteca ou da aplicação
     * do usuário.
     */
    App::import("App", array("config/settings", "config/routes", "config/database"));
    App::import("Controller", "app_controller");
    App::import("Model", "app_model");
    
?>