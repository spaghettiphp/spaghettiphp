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
     *  @return resource Conexão com o banco de dados
     */
    public function connect() {
        $this->connection = mysql_connect($this->config["host"], $this->config["user"], $this->config["password"]);
        if(mysql_select_db($this->config["database"], $this->connection)):
            $this->connected = true;
        endif;
        return $this->connection;
    }
    /**
     *  Desconecta do banco de dados.
     *
     *  @return boolean Verdadeiro caso a conexão tenha sido desfeita
     */
    public function disconnect() {
		if(mysql_close($this->connection)):
			$this->connected = false;
			$this->connection = null;
		endif;
		return !$this->connected;
    }
	/**
	 *  Retorna a conexão com o banco de dados, ou conecta caso a conexão ainda
	 *  não tenha sido estabelecida.
	 *
	 *  @return resource Conexão com o banco de dados
	 */
    public function &getConnection() {
		if(!$this->connected):
			$this->connect();
		endif;
        return $this->connection;
    }
	/**
	 *  Short description.
	 *
	 *  @param string $sql Consulta SQL a ser executada
	 *  @return mixed Resultado da consulta
	 */
	public function query($sql) {
		return mysql_query($sql, $this->getConnection());
	}
}

?>