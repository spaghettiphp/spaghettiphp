<?php
/**
 *  HomeController é o controlador padrão do Spaghetti, já definido com algumas
 *  configurações para que você possa ver a tela inicial do Spaghetti, e já começar
 *  a desenvolver sem nenhuma tela de erro.
 */

class HomeController extends AppController {
    /**
     * Para que você não receba o erro de Model não existente, o HomeController
     * está configurado para não usar modelo algum. Basta remover a linha abaixo
     * e ele passará a se comportar da forma que você espera.
     */
    public $uses = array();
    public function index() {
        
    }
}

?>