<?php
/**
 * Esse é o arquivo das principais configurações do Spaghetti. Através delas,
 * você pode configurar o comportamento do núcleo do Spaghetti.
 */

/**
 * defaultExtension define a extensão de arquivo padrão quando nenhuma outra for
 * usada na URL da requisição. Desta maneira, seus arquivos de views deverão se chamar
 * nome_do_arquivo.EXTENSAO.php
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
 *  appEncoding define a codificação a ser enviada ao navegador pela aplicação.
 */
Config::write("appEncoding", "utf-8");

/**
 *  securitySalt é uma string usada na criptografia de dados.
 */
Config::write("securitySalt", "12345678910111213141516");

?>