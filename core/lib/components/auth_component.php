<?php
/**
 *  AuthComponent é o responsável pela autenticação e controle de acesso na aplicação,
 *  podendo controlar com base nos prefixos e nas classes do controller.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class AuthComponent extends Component {
    /**
     *  Permissões dos controllers e actions corrente.
     *  
     *  @var array
     */
    public $permissions = null;
    /**
     *  Mantém o estado do usuário corrente.
     *  
     *  @var boolean
     */
    public $loggedIn = false;
    /**
     *  Objeto Controller.
     * 
     *  @var object
     */
    public $controller = null;
    /**
     *  Dados repassados pelo controller (via $_POST).
     *  
     *  @var array
     */
    public $data = array();
    /**
     *  Parâmetros do controller.
     *  
     *  @var array
     */
    public $params = array();
    /**
     *  Nome do modelo a ser utilizado para a autenticação.
     * 
     *  @var string
     */
    public $userModel = "Users";
    /**
     *  Condições adicionais para serem usadas na autenticação.
     * 
     *  @var array
     */
    public $userScope = array();
    /**
     *  URL a ser redirecionada e controlar o login.
     * 
     *  @var string
     */
    public $loginAction = "/users/login";
    /**
     *  URL a ser redirecionada após o login efetuado com sucesso.
     * 
     *  @var string
     */
    public $loginRedirect = "/";
    /**
     *  URL a ser redirecionado após o logout
     * 
     *  @var string
     */
    public $logoutRedirect = "/";
    /**
     *  Permite especificar campos para usuário e senha diferentes do padrão.
     *
     *  @var array
     */
    public $fields = array(
        "username" => "username",
        "password" => "password"
    );
    /**
     *  Inicializa o componente.
     * 
     *  @param object $controller
     */
     public function initialize(&$controller) {
        $this->controller = $controller;
        $this->params = array(
            "prefix" => $controller->params["prefix"],
            "controller" => $controller->params["controller"],
            "action" => $controller->params["action"]
        );
        $this->data = $controller->data;
        if($this->permissions === null):
            $this->permissions = array(
                "prefix" => array(),
                "controller" => array("users" => true),
                "action" => array()
            );
        endif;
    }
    /**
     *  Verifica se o usuário esta autorizado ou não para acessar o controller.
     * 
     *  @return boolean Verdadeiro caso esteja autorizado a acessar o controller
     */
    public function authorized() {
        $authorized = true;
        if(isset($_COOKIE["user_id"]) && isset($_COOKIE["user_password"])):
            $data = array("id" => $_COOKIE["user_id"], "password" => $_COOKIE["user_password"]);
            $identify = $this->identify($data);
            if(!empty($identify)):
                $this->loggedIn = true;
                return true;
            endif;
        endif;
        foreach($this->params as $param => $value):
            if(isset($this->permissions[$param][$value])):
                if($this->permissions[$param][$value] === false):
                    $authorized = false;
                elseif($this->permissions[$param][$value] === true):
                    $authorized = true;
                endif;
            endif;
        endforeach;
        return $authorized;
    }
    /**
     *  Verifica se o usuário esta autorizado ou não para o controller,
     *  redirecionando para a pagina de login em caso negativo.
     * 
     *  @return boolean Verdadeiro se estiver autorizado a acessar o controller
     */
    public function check() {
        if(!$this->authorized()):
            $this->controller->redirect($this->loginAction);
            return false;
        endif;
        return true;
    }
    /**
     *  Libera prefixos/controllers a serem visualizados sem autenticação.
     * 
     *  @param array $permissions Prefixos/controllers a serem liberados
     *  @return true
     */
    public function allow($permissions = array()) {
        if($permissions == "" || $permissions == "*"):
            $this->permissions["prefix"][""] = true;
        else:
            foreach($permissions as $resource => $permission):
                $this->permissions[$resource][$permission] = true;
            endforeach;
        endif;
        return true;
    }
    /**
     *  Bloqueia os prefixos/controller a serem visualizados com autenticação.
     *  
     *  @param array $permissions Prefixos/controllers a serem bloqueados
     *  @return true
     */
    public function deny($permissions = array()) {
        if($permissions == "" || $permissions == "*"):
            $this->permissions["prefix"][""] = false;
        else:
            foreach($permissions as $resource => $permission):
                $this->permissions[$resource][$permission] = false;
            endforeach;
        endif;
        return true;
    }
    /**
     *  Criptografa a senha com o hash MD5.
     * 
     *  @param array $data Dados a serem utilizados
     *  @return array Dados com senha criptografada com hash MD5
     */
    public function hashPasswords($data = array()) {
        if(isset($data[$this->fields["password"]])):
            $data[$this->fields["password"]] = md5($data[$this->fields["password"]]);
        endif;
        return $data;
    }
    /**
     *  Carrega os dados do usuário.
     * 
     *  @param array $data Dados providos pelo usuário
     *  @return array Dados do usuário
     */
    public function identify($data = array()) {
        $userModel = ClassRegistry::init($this->userModel);
        $user = $userModel->first(array_merge($this->userScope, $data));
        return $user;
    }
    /**
     *  Verifica os dados repassados para realizar o login no sistema.
     * 
     *  @return boolean Verdadeiro para login efetuado com sucesso
     */
    public function login() {
        if(!$this->loggedIn):
            if(!empty($this->data)):
                $user = $this->identify($this->hashPasswords($this->data));
                if(empty($user)):
                    $this->controller->set("authError", "wrongData");
                    return false;
                else:
                    setcookie("user_id", $user["id"], null, "/");
                    setcookie("user_password", $user[$this->fields["password"]], null, "/");
                    $this->loggedIn = true;
                    $this->controller->redirect($this->loginRedirect);
                    return true;
                endif;
            endif;
        else:
            $this->controller->redirect($this->loginRedirect);
        endif;
    }
    /**
     *  Efetua logout, redirecionando em seguida.
     * 
     *  @return true
     */
    public function logout() {
        setcookie("user_id", "", time() - 3600, "/");
        setcookie("user_password", "", time() - 3600, "/");
        $this->loggedIn = false;
        $this->controller->redirect($this->logoutRedirect);
        return true;
    }
    /**
     *  Pega o usuário da sessão.
     * 
     *  @param string $field Campo a ser retornado
     *  @return array Repassa um array com os dados do usuário ou apenas o camporepassado por $field
     */
    public function user($field = null) {
        $user_id = $_COOKIE["user_id"];
        $user = $this->identify(array("id" => $user_id));
        return is_null($field) ? $user : $user[$field];
    }
}

?>