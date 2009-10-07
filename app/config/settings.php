<?php
/**
 * Esse é o arquivo das principais configurações do Spaghetti. Através delas,
 * você pode configurar o comportamento do núcleo do Spaghetti.
 */

/**
 * defaultExtension define a extensão de arquivo padrão quando nenhuma outra for
 * usada na URL da requisição.
 */
Config::write("defaultExtension", "htm");

/**
 * Com o environment, você pode escolher qual ambiente de desenvolvimento está
 * utilizando. É principalmente utilizado na configuração de banco de dados,
 * evitando que você tenha que redefiní-las a cada deploy.
 */
Config::write("environment", "development");

/**
 *  debugMode define o nível de mensagens de erro que você receberá enquanto
 *  estiver desenvolvendo.
 */
Config::write("debugMode", 1);

/**
 *  Short description.
 */
Config::write("appEncoding", "utf-8");

/**
 *  Short description.
 */
Config::write("securitySalt", "pokipoki");

?>