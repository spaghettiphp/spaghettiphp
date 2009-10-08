<?php
/**
 * Esse arquivo é onde você pode definir rotas e prefixos para sua aplicação.
 * Rotas são usadas para direcionar URLs para determinadas partes da aplicação,
 * sem que você precise renomear controllers e actions. Já os prefixos permitem
 * que você separe diversas partes da aplicação, como um painel de administração,
 * por exemplo.
 * 
 */

/**
 * Essa rota define o controller padrão de sua aplicação, aquele que o usuário
 * verá toda vez que acessar a raíz de seu sistema. Você pode escolher o controller
 * que mais fizer sentido para você
 */
Mapper::root("home");

/**
 * Caso você precise de um painel de administração, você pode descomentar a linha
 * abaixo. Você também pode adicionar quantos prefixos mais forem necessários.
 */
Mapper::prefix("admin");

?>