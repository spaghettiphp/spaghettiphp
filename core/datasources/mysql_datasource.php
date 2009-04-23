<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class MysqlDatasource extends Datasource {
    private $connection;
    public $connected = false;
    /**
     *  Conecta ao banco de dados.
     *
     *  @return boolean Verdadeiro se a conexão foi estabelecida
     */
    public function connect() {
        $this->connection = mysql_connect($this->config["host"], $this->config["user"], $this->config["password"]);
        if(mysql_select_db($this->config["database"], $this->connection)):
            $this->connected = true;
        endif;
        return $this->connected;
    }
    /**
     *  Desconecta do banco de dados.
     *
     *  @return boolean Verdadeiro caso a conexão tenha sido desfeita
     */
    public function disconnect() {
		$this->connected = !mysql_close($this->connection);
		return !$this->connected;
    }
    public function &getConnection() {
        return ($this->connected) ? $this->connection : false;
    }
}

?>