<?php
/**
 *  AuthComponent é o responsável pela autenticação e controle de acesso na aplicação.
 * 
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class AuthComponent extends Component {
    /**
     *  Autorização para URLs não especificadas explicitamente.
     */
    public $authorized = true;
    /**
      *  Define se AuthComponent::check() será chamado automaticamente.
      */
    public $autoCheck = true;
    /**
     *  Instância do controller.
     */
    public $controller;
    /**
      *  Nomes dos campos do modelo a serem usados na autenticação.
      */
    public $fields = array(
        "id" => "id",
        "username" => "username",
        "password" => "password"
    );
    /**
      *  Método de hash a ser usado para senhas.
      */
    public $hash = "sha1";
    /**
      *  Estado de autenticação do usuário corrente.
      */
    public $loggedIn;
    /**
      *  Action que fará login.
      */
    public $loginAction = "/users/login";
    /**
      *  URL para redirecionamento após o login.
      */
    public $loginRedirect = "/";
    /**
      *  Action que fará logout.
      */
    public $logoutAction = "/users/logout";
    /**
      *  URL para redirecionamento após o logout.
      */
    public $logoutRedirect = "/";
    /**
     *  Lista de permissões.
     */
    public $permissions = array();
    /**
      *  Usuário atual.
      */
    public $user = array();
    /**
      *  Nome do modelo a ser utilizado para a autenticação.
      */
    public $userModel = "Users";
    /**
      *  Condições adicionais para serem usadas na autenticação.
      */
    public $userScope = array();
    /**
      *  Define se o salt será usado como prefixo das senhas.
      */
    public $useSalt = true;
    /**
      *  Data de expiração do cookie.
      */
    public $expires;
    /**
      *  Caminho para o qual o cookie está disponível.
      */
    public $path = "/";
    /**
      *  Domínio para ao qual o cookie está disponível.
      */
    public $domain = "";
    /**
      *  Define um cookie seguro.
      */
    public $secure = false;

    /**
      *  Inicializa o component.
      *
      *  @param object $controller Objeto Controller
      *  @return void
      */
    public function initialize(&$controller) {
        $this->controller = $controller;
    }
    /**
      *  Faz as operações necessárias após a inicialização do controller.
      *
      *  @param object $controller Objeto Controller
      *  @return void
      */
    public function startup(&$controller) {
        $this->allow($this->loginAction);
        if($this->autoCheck):
            $this->check();
        endif;
    }
    /**
      *  Finaliza o component.
      *
      *  @param object $controller Objeto Controller
      *  @return void
      */
    public function shutdown(&$controller) {
        if(Mapper::match($this->loginAction)):
            $this->login();
        endif;
    }
    /**
      *  Verifica se o usuário está autorizado a acessar a URL atual, tomando as
      *  ações necessárias no caso contrário.
      *
      *  @return boolean Verdadeiro caso o usuário esteja autorizado
      */
    public function check() {
        if(!$this->authorized()):
            Cookie::write("action", Mapper::here());
            $this->controller->redirect($this->loginAction);
            return false;
        endif;
        return true;
    }
    /**
     *  Verifica se o usuário esta autorizado ou não para acessar a URL atual.
     *
     *  @return boolean Verdadeiro caso o usuário esteja autorizado
     */
    public function authorized() {
        if($this->loggedIn()):
            return true;
        else:
            $here = Mapper::here();
            $authorized = $this->authorized;
            foreach($this->permissions as $url => $permission):
                if(Mapper::match($url, $here)):
                    $authorized = $permission;
                endif;
            endforeach;
            return $authorized;
        endif;
    }
    /**
     *  Libera URLs a serem visualizadas sem autenticação.
     *
     *  @param string $url URL a ser liberada
     *  @return void
     */
    public function allow($url = null) {
        if(is_null($url)):
            $this->authorized = true;
        else:
            $this->permissions[$url] = true;
        endif;
    }
    /**
     *  Bloqueia os URLS para serem visualizadas apenas com autenticação.
     *
     *  @param string $url URL a ser bloqueada
     *  @return void
     */
    public function deny($url = null) {
        if(is_null($url)):
            $this->authorized = false;
        else:
            $this->permissions[$url] = false;
        endif;
    }
    /**
     *  Verifica se o usuário está autenticado.
     *
     *  @return boolean Verdadeiro caso o usuário esteja autenticado
     */
    public function loggedIn() {
        if(is_null($this->loggedIn)):
            $user = Cookie::read("user_id");
            $password = Cookie::read("password");
            if(!is_null($user) && !is_null($password)):
                $user = $this->identify(array(
                    $this->fields["id"] => $user,
                    $this->fields["password"] => $password
                ));
                $this->loggedIn = !empty($user);
            else:
                $this->loggedIn = false;
            endif;
        endif;
        return $this->loggedIn;
    }
    /**
      *  Identifica o usuário no banco de dados.
      *
      *  @param array $conditions Condições da busca
      *  @return array Dados do usuário
      */
    public function identify($conditions) {
        $userModel = ClassRegistry::load($this->userModel);
        if(!$userModel):
            $this->error("missingModel", array("model" => $this->userModel));
            return false;
        endif;
        $params = array(
            "conditions" => array_merge(
                $this->userScope,
                $conditions
            )
        );
        return $this->user = $userModel->first($params);
    }
    /**
      *  Cria o hash de uma senha.
      *
      *  @param string $password Senha para ter o hash gerado
      *  @return string Hash da senha
      */
    public function hash($password) {
        return Security::hash($password, $this->hash, $this->useSalt);
    }
    /**
      *  Efetua o login do usuário.
      *
      *  @return void
      */
    public function login() {
        if(!$this->loggedIn()):
            if(!empty($this->controller->data)):
                $password = $this->hash($this->controller->data[$this->fields["password"]]);
                $user = $this->identify(array(
                    $this->fields["username"] => $this->controller->data[$this->fields["username"]],
                    $this->fields["password"] => $password
                ));
                if(!empty($user)):
                    Cookie::set("domain", $this->domain);
                    Cookie::set("path", $this->path);
                    Cookie::set("secure", $this->secure);
                    Cookie::write("user_id", $user[$this->fields["id"]], $this->expires);
                    Cookie::write("password", $password, $this->expires);
                    $redirect = Cookie::read("action");
                    if(is_null($redirect)):
                        $redirect = $this->loginRedirect;
                    else:
                        Cookie::delete("action");
                    endif;
                    $this->controller->redirect($redirect);
                else:
                    $this->error("loginFailed");
                endif;
            endif;
        else:
            $this->controller->redirect($this->loginRedirect);
        endif;
    }
    /**
      *  Efetua o logout do usuário.
      *
      *  @return void
      */
    public function logout() {
        Cookie::delete("user_id");
        Cookie::delete("password");
        $this->controller->redirect($this->logoutRedirect);
    }
    /**
      *  Retorna informações do usuário.
      *
      *  @param string $field Campo a ser retornado
      *  @return mixed Campo escolhido ou todas as informações do usuário
      */
    public function user($field = null) {
        if(empty($this->user)):
            return null;
        endif;
        if(is_null($field)):
            return $this->user;
        else:
            return $this->user[$field];
        endif;
    }
    /**
      *  Define um erro ocorrido durante a autenticação.
      *
      *  @param string $error Nome do erro
      *  @return void
      */
    public function error($type, $details = array()) {
        $this->controller->set("authError", $type);
    }
}

?>