<?php
/**
 *  Short description.
 *
 *  @author	   José Cláudio Medeiros de Lima <contato@claudiomedeiros.net>
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2009, José Cláudio Medeiros de Lima <contato@claudiomedeiros.net>
 *
 */

class UserAccessComponent extends Component{
    /**
      *  Configurações de acesso aos dados dos usuários.
      */
    public $configs = array(
        "username" => "username",        //    Campo que contém o username
        "password" => "password",        //    Campo que contém a senha codificada com MD5
        "level" => "level"            //    Campo que contém o nível do usuário
    );
    /**
      *  Model padrão para ser usado no UA.
      */
    public $model = "Users";
    /**
      *  Url onde será realizado o login.
      */
    public $loginAction = "/users/login";
    /**
      *  Redirecionamento após o logout ser realizado.
      */
    public $logoutRedirect = "/";
    /**
      *  Redireciona para a página que está tentando acessar.
      */
    public $loginRedirect = "/";
    /**
      *  Se tiver true, e o usuário tentar acessar uma página que é proibida, será
      *  direcionado para o login, e após o login, será direcionado para essa página
      *  que tentou acessar.
      */
    public $autoRedirect = true;
    /**
      *  Guarda os erros gerados. Só um, pra ser sincero, mas torna integrável ao ValidationComponent.
      */
    public $errors = null;
    /**
      *  Está autorizado a acessar?
      */
    public $authorized = true;
    /**
      *  O level atualmente sendo modificado.
      */
    public $now_level = null;
    /**
      *  A duração dos cookies, em segundos.
      */
    public $expires = null;
    /**
      *  Array de condições, onde é possível limitar o login para condições específicas.
      */
    public $userScope = array();
    /**
      *  Define as permissões de acesso para as urls.
      */
    public $permissions = array(
        "all" => array(
            "/" => true,
            "/users/login" => true,
            "/users/register" => true,
        ),
        "logged" => array(
            "/users" => true,
            "/logout" => true
        )
    );
    
    /**
      *  Inicialização do component.
      *
      *  @param object $controller Controller usando o componente
      */
    public function initialize(&$controller) {
        $this->controller = $controller;
        $this->data = $controller->data;
    }
    /**
      *  Verifica se o usuário pode acessar a página atual.
      *
      *  @return boolean Verdadeiro caso o usuário tenha acesso a página atual
      */
    public function check() {
        $authorized = false;
        if($this->loggedIn()): 
            if($this->user($this->configs["level"])):
                $authorized = $this->loopLevels($this->user($this->configs["level"]), $authorized);
                if($authorized):
                    return true;
                endif;
            endif;
            $authorized = $this->loopLevels("logged", $authorized);
            if($authorized):
                return true;
            endif;
        endif;
        $authorized = $this->loopLevels("all", $authorized);
        if($authorized):
            return true;
        endif;
        if($this->autoRedirect):
            $this->controller->redirect($this->loginAction . "?action=" . Mapper::here());
        else:
            $this->controller->redirect($this->loginAction);
        endif;
        return false.
    }
    /**
      *  Dá um loop nas permissões para o nível de acesso informado em $level.
      *
      *  @param string $level O nível que está sendo verificado
      *  @param boolean $authorized Valor recebido de $this->check(), que corresponde
      *         à verificação previamente feita, já que loopLevels será chamado
      *         várias vezes por check, esse parâmetro tem a função de conservar
      *         o valor previamente constatado, e utilizando na definição do próximo
      *  @return boolean
      */
    private function loopLevels($level, $authorized) {
        foreach($this->permissions[$level] as $url => $permission):
            if($this->match($url)):
                $authorized = $permission;
            endif;
        endforeach;
        return $authorized;
    }
    /**
      *  Faz a consulta pra saber se os dados de login batem com os dados do banco
      *  de dados. Faz a mesclagem dos dados de login com o userScope.
      *
      *  @return boolean Dados do usuário, ou falso caso não exista
      */
    public function identify() {
        $conditions = array_merge(
            $this->userScope, 
            array(
                $this->configs["username"] => $this->data[$this->configs["username"]], 
                $this->configs["password"] => md5($this->data[$this->configs["password"]])
            )
        );
        $user = $this->objModel()->first(array("conditions" => $conditions));
        return empty($user) ? false : $user;
    }
    /**
      *  Retorna os dados do usuário logado.
      *
      *  @param string $var O nome do campo do usuário que se quer saber
      *  @return mixed Dados do usuário
      */
    public function user($var = null) {
        $user = $this->objModel()->first(array(
            "conditions" => array("id" => $_COOKIE["user_id"])
        ));
        if(!empty($var) && isset($user[$var])):
            return $user[$var];
        elseif(empty($var)):
            return $user;
        endif;
        return false;
    }
    /**
      *  Alimenta o array de permissões.
      *
      *  @param string $url Url que será permitida o acesso para o nível atual
      *  @return object $this
      */
    public function allow($url) {
        $this->permissions[$this->now_level][$url] = true;
        return $this;
    }
    /**
      *  Alimenta o array de permissões negadas.
      *
      *  @param string $url Url que será negada o acesso para o nível atual
      *  @return object $this
      */
    public function deny($url) {
        $this->permissions[$this->now_level][$url] = false;
        return $this;
    }
    /**
      *  Efetua o login, baseado nos dados passados pelo usuário.
      *
      *  @return booelan Verdadeiro em caso de login efetuado com sucesso
      */
    public function login() {
        if(!empty($this->controller->data)):
            $user = $this->identify();
            if($user):
                setcookie("user_id", $user['id'], $this->expires, '/');
                setcookie("user_password", $user[$this->configs['password']], $this->expires, '/');
                return true;
            else:
                $this->error('authError');
                return false;
            endif;
        endif;
    }
    /**
      *  Efetua o logout, excluindo cookies.
      *
      *  @return true
      */
    public function logout() {
        setcookie("user_id", "", time() - 3600, "/");
        setcookie("user_password", "", time() - 3600, "/");
        $this->redirect($this->logoutRedirect);
    }
    /**
      *  Verifica se o usuário está logado.
      *
      *  @return boolean Verdadeiro caso o usuário esteja logado
      */
    public function loggedIn() {
        return isset($_COOKIE["user_id"]) && isset($_COOKIE["user_password"]);
    }
    /**
      *  Define o foco do encadeamento para o nível especificado em $level. Todas
      *  permissões e negações feitas serão acrescentadas nesse nível, até que
      *  outro nível seja definido.
      *
      *  @param string $level O número/nome do nível que iremos acrescentar permissões/negações
      *  @return object $this
      */
    public function userLevel($level) {
        $this->now_level = $level;
        return $this;
    }
    /**
      *  Traz o foco do encadeamento para o nível mais baixo, ou seja, todos os usuários/visitantes, etc
      *
      *  @return object $this
      */
    public function allLevels() {
        $this->userLevel("all");
        return $this;
    }    
    /**
      *  Traz o foco do encadeamento para o nível dos usuários logados.
      *
      *  @return object $this
      */
    public function allLogged() {
        $this->userLevel("logged");
        return $this;
    }
    /**
      *  Adiciona um login redirect para cada nível de usuário.
      *
      *  @return object $this
      */
    public function loginRedirect($url) {
        $this->loginRedirect[$this->now_level] = $url;
        return $this;
    }
    /**
      *  Define as configurações padrão.
      *
      *  @param mixed $key Pode receber a chave a ser alterada, ou um array com as
      *         configurações. Se for passado a chave, é necessário informar o
      *         parâmetro $value, para alterar o valor, ou então $value receberá
      *         null. Caso $key seja um array, o parâmetro $value não será necessário,
      *         pois esse array já deverá conter todas as chaves que serão alteradas
      *         e seus respectivos valores
      *  @param string $value O novo valor para a chave informada
      *  @return object $this
      */
    public function config($key, $value = null) {
        if(is_array($key)):
            $this->configs = array_merge($this->configs, $key);
        else:
            $this->configs[$key] = $value;
        endif;
        return $this;
    }
    /**
      *  Salva o novo model nas configurações.
      *
      *  @return object $this
      */
    public function model($model) {
        $this->model = $model;
        return $this;
    }
    /**
      *  Retorna o objeto model, abstraindo as chamadas a ClassRegistry::init()
      *
      *  @return object
      */
    public function objModel() {
        return ClassRegistry::init($this->model);
    }
    /**
      *  Retorna a página atual, sem uma possível barra final.
      *
      *  @return string
      */
    public function here() {
        return "/" . trim(Mapper::here(), "/");
        $base = array("prefix", "controller", "action");
    /**
        // Essa solução abaixo, é para resolver a questão da ambiguidade, 
        // e remoção de declarações duplas, explicadas no e-mail.
        // Da forma acima, "/login" é diferente de "/users/login",  podendo 
        //obrigar ao desenvolvedor declarar as duas, ou deixar uma delas aberta 
        //a acesso, caso ele não atente.
        // A forma abaixo pega os componentes da url, e gera a url que será 
        //usada pelos outros métodos do component.
        
        foreach($base as $urlComponents):
            if($urlComponents=="action"):
                if($this->controller->params["action"]=="index"):
                    continue;
                endif;
            endif;
            $buff . = "/" . $this->controller->params[$urlComponents];
        endforeach;
        return "/" . trim($buff, "/");
    */
    }
    /**
      *  Abstrai a criação das mensagens de erro.
      *
      *  @return true
      */
    public function error($key) {
        $this->errors = "wrongData";
        $this->controller->set("authError", $this->errors);
        return true;
    }
    /**
      *  Verifica se a url informada, é a mesma url atual, ou uma url pai.
      *
      *  @return boolean
      */
    public function match($url) {
        $url = "/" . trim($url, "/");
        return substr($this->here(), 0, strlen($url)) == $url;
    }
}

?>