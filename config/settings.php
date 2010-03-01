<?php
/**
 *  Esse é o arquivo das principais configurações do Spaghetti. Através delas,
 *  você pode configurar o comportamento do núcleo do Spaghetti.
 */

/**
 *  defaultExtension define a extensão de arquivo padrão quando nenhuma outra for
 *  usada na URL da requisição. Desta maneira, seus arquivos de views deverão se chamar
 *  nome_do_arquivo.extensao.php
 */
Config::write('defaultExtension', 'htm');

/**
 *  Com o environment, você pode escolher qual ambiente de desenvolvimento está
 *  utilizando. É principalmente utilizado na configuração de banco de dados,
 *  evitando que você tenha que redefiní-las a cada deploy.
 */
Config::write('environment', 'development');

/**
 *  securitySalt é uma string usada na criptografia de dados. Esta string não
 *  tem limite de caracteres e aceita qualquer tipo de caracter.
 */
Config::write('securitySalt', '37b1ffe6afe7577a90f1ac2098605d5711fdc59f');