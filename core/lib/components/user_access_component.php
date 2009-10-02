<?
	/**
	@author: José Cláudio Medeiros de Lima <contato@claudiomedeiros.net>
	@version: 0.1
	@date: 24/08/2009
	
	Licensed under The MIT License.
	Redistributions of files must retain the above copyright notice.

	@license http://www.opensource.org/licenses/mit-license.php The MIT License
	
	-------------------------------------------------------------------------------
	|	OBJETIVOS																						|
	-------------------------------------------------------------------------------
	Tem objetivo de criar uma forma mais simples de fazer login de usuários e
	controle de acesso às partes da aplicação
	
	-------------------------------------------------------------------------------
	|	COMO USAR																						|
	-------------------------------------------------------------------------------
	$ua = $this->UserAccessComponent
	$ua = $this->model('Usuarios');	//	Só é necessário, caso seja diferente de Users
	$ua->config(array(					//	Configurações: Os campos padrão só precisam ser definidos, caso sejam diferentes dos seguintes
		'username'	=> 'username',		//	Campo que contém o username.
		'password'	=> 'password'		//	Campo que contém a senha codificada com MD5
		'level'		=> 'level'			//	Campo que contém o nível do usuário
	));
	
	$ua->userLevel(0)->allow("/imoveis") //O deny será geral para todo mundo, o allow libera
		->userLevel(1)->allow("*")			//para o usuário, cujo level seja 1, será liberado tudo
		->userLevel(2)->allow('/admin')  //para o usuário cujo level seja 2, será liberado só o admin
						  ->allow(/imoveis)
	->check();

	-------------------------------------------------------------------------------
	|	COMO FAZER																						|
	-------------------------------------------------------------------------------
	
	-	deny() e allow()  criarão chaves no mesmo array, com valores true or false,
		dependendo da permissão de acesso. As chaves desse array serão o endereço da
		página. Ex.: deny('/admin') nega acesso a tudo do admin. allow(/admin/noticias)
		cria a exceção ao deny, liberando o acesso ao /admin/noticias. outro deny(/admin/noticias/cadastrar)
		criaria nova negação para essa url. Concluindo, o importante, para as regras
		que se sobrescrevem, é a sequência de definição.
	
	-------------------------------------------------------------------------------
	|	TO DO																								|
	-------------------------------------------------------------------------------
	
	
	-	Se tentar acessar uma página proibida, e for direcionado para o login, guardar
		o valor dessa página para ser direcionado para ela após fazer o login

	-	Auto redirecionamentos individuais para cada nível de usuário

	*/
	
	class UserAccessComponent extends Component{

		/**
		 *	Configurações de acesso aos dados dos usuários
		 */
		public $configs = array(
			'username'	=> 'username',		//	Campo que contém o username
			'password'	=> 'password',		//	Campo que contém a senha codificada com MD5
			'level'		=> 'level',			//	Campo que contém o nível do usuário
		);

		/**
		 *	Model padrão para ser usado no UA
		 */
		public $model = 'Users';

		/**
		 *	Url onde será realizado o login
		 */
		public $loginAction = '/users/login';

		/**
		 *	Redirecionamento após o logout ser realizado
		 */
		public $logoutRedirect = '/';

		/**
		 *	Redireciona para a página que está tentando acessar.
		 */
		public $loginRedirect = '/';

		/**
		 *	Se tiver true, e o usuário tentar acessar uma página que é proibida, será direcionado 
		 *	para o login, e após o login, será direcionado para essa página que tentou acessar
		 */
		public $autoRedirect = true;

		/**
		 *	Guarda os erros gerados. Só um, pra ser sincero, mas torna integrável ao ValidationComponent.
		 */
		public $errors = null;

		/**
		 *	Está autorizado a acessar?
		 */
		public $authorized = true;

		/**
		 *	O level atualmente sendo modificado
		 */
		public $now_level = null;

		/**
		 *	A duração dos cookies, em segundos
		 */
		public $expires = null;

		/**
		 *	userScope é um array de condições, onde é possível limitar o login para condições específicas
		 *	Ex.:
		 *		$this->userScope('nivel'=>1) //Só permite o login de usuários com nivel=1
		 *	Segue a mesma lógica do userScope do Auth original. As definições guardadas aqui, 
		 *	serão utilizadas juntamente com o código que faz o login, no caso acima, seriam verificadas
		 *	além do usuário e senha, o campo nivel também, que deveria ser igual a 1.
		 */
		public $userScope = array();

		/**
		 *	Define as permissões de acesso para as urls.
		 *	As definições de acesso são baseadas em níveis de acesso, através do método userLevel($id_level),
		 *	onde $id_level="all" equivale a todos os visitantes, "logged" a todos os visitantes logados, e qualquer
		 *	outro valor para indicar o valor do campo "level" no banco de dados do usuário. 
		 * Ex.:
		 *	$ua = $this->UserAccessComponent;
		 *	$ua->userLevel("all")->allow("/imoveis")	// Libera o acesso ao controller (ou prefix) imoveis a todos os usuários/visitantes do site
		 *		->userLevel("logged")->allow("/my")		// Libera para todos os usuários logados o controller/prefix "my".
		 *		->userLevel("authors")->allow("/admin/noticias")			// Libera para todos os usuários, onde, na tabela "users", conste
		 *															// que o campo "level" seja igual a "authors"
		 *		->userLevel("admin")->allow("/")			// Libera tudo para todos os usuários, onde, na tabela "users", conste
		 *															// que o campo "level" seja igual a "admin"
		 *	As duas chaves padrão são "all" e "logged". As demais serão definidas pelo valor constante no model "Users".
		 *	Para definir as permissões para "all", pode-se usar o método userLevel("all"), ou allLevel(), que é um apelido
		 *	para o primeiro. Da mesma forma, pode-se usar allLogged() para o mesmo efeito que userLevel("logged"), ou seja, 
		 *	definir permissões para todos os usuários logados.
		 *	
		 *	A forma de definição será baseada na sequência de declaração, mas com prioridade para as declarações com nível mais alto,
		 *	ou seja, se o usuário tiver um nível "author", por exemplo, suas permissões serão avaliadas antes dos "logged" e dos "all"
		 *	
		 *	Obs.: permissions será baseada no endereço real, não levando em consideração as reescritas feita nos ROUTES, nesse caso
		 *	ainda que a página de login, por exemplo, tenha sido reescrita para /login, ela será tratada como /users/login
		 *	Julio, a observação acima, está depreciada. O que você acha da opção? Eu acho mais segura, pois da forma atual, 
		 * se bloquear /login, não bloquearia /users/login, por exemplo.
		 *	
		 */
		public $permissions = array(
			'all'		=> array(
				'/'					=> true,
				'/users/login'		=> true,
				'/users/register'	=> true,
			),
			'logged'	=> array(
				'/users'				=> true,
				'/logout'			=> true
			),
		);
		
		/**
		 *	Inicialização do component
		 *	Crio um object controller, e pego o valor do campo data
		 */
		public function initialize(& $controller){
			$this->controller = $controller;
			$this->data = $controller->data;
		}
		
		/**
		 *	Verifico se o usuário pode acessar a página atual
		 *	
		 *	1)	Está logado?
		 *		1.1)	Tem nível definido no banco de dados?
		 *			1.1.1)	Tem nível definido: Aplico as permissões do nível
		 *			1.1.2)	Não tem nível: Aplico as permissões de allLogged
		 *	2) Não está logado: Só pode acessar as páginas definidas em "all"
		 *	Em todos os casos, as permissões vêm do maior para o menor
		 */
		public function check(){
			pr($this->user());
			$authorized = false;
			//está logado
			if($this->loggedIn()): 
				//tem nível definido?
				if($this->user($this->configs['level'])):
					//loop pelas permissões de nível
					$authorized = $this->loopLevels($this->user($this->configs['level']), $authorized);
					if($authorized) return true;
				endif;
				//loop pelas permissões para usuário logados.
				$authorized = $this->loopLevels('logged', $authorized);
				if($authorized) return true;
			endif;
			//não estou logado, logo vou analisar as permissões contidas em $this->permissions['all']
			$authorized = $this->loopLevels('all', $authorized);
			if($authorized) return true;
			
			//Se não foi autorizado até agora, já era.
			//Se o autoRedirecionamento estiver ligado
			if($this->autoRedirect):
				$this->controller->redirect($this->loginAction . '?action=' . Mapper::here());
			else:
				$this->controller->redirect($this->loginAction);
			endif;
		}
		
		/**
		 *	Dá um loop nas permissões para o nível de acesso informado em $level
		 *	
		 *	@param $level O nível que está sendo verificado.
		 *	@param $authorized É um valor recebido de $this->check(), que corresponde à 
		 *	verificação previamente feita, já que loopLevels será chamado várias vezes 
		 *	por check, esse parâmetro tem a função de conservar o valor previamente 
		 *	constatado, e utilizando na definição do próximo.
		 *	@return bool
		 */
		private function loopLevels($level, $authorized){
			foreach($this->permissions[$level] as $url=>$permission):
				if($this->match($url)):
					$authorized = $permission;
				endif;
			endforeach;
			return $authorized;
		}
		
		/**
		 *	Faz a consulta pra saber se os dados de login batem com os dados do banco de dados
		 *	Faz a mesclagem dos dados de login com o userScope
		 *	
		 *	@return boolean False caso não existam usuários com esses dados, e os dados do usuário, caso exista.
		 *	
		 */
		public function identify(){
			$conditions = array_merge($this->userScope, 
				array(
					$this->configs['username'] => $this->data[$this->configs['username']], 
					$this->configs['password'] => md5($this->data[$this->configs['password']]),
				)
			);
			$user = $this->objModel()->first(array('conditions' => $conditions));
			return empty($user) ? false : $user;
		}
		/**
		 *	Retorna os dados dos usuários logados
		 *	
		 *	@param string $var O nome do campo do usuário que se quer saber
		 *	@return mixed Se $var=null, retorno todos os dados do usuário. Se $var for o nome d
		 *					e um campo do banco de dados de usuário, retorno seu valor
		 */
		public function user($var=null){
			$user = $this->objModel()->first(array(
				'conditions' => array('id' => $_COOKIE['user_id'])
				));
			if(!empty($var) && isset($user[$var])):
				return $user[$var];
			elseif(empty($var)):
				return $user;
			endif;
			return false;
		}
		
		/**
		 *	Alimento o array de permissões
		 *	Ex.:
		 *		$this->allow('/imoveis') dá permissão para qualquer página filha de '/imoveis'
		 *	Essa permissão pode ser negada caso deny() seja definido em seguida, com, por exemplo, deny('imoveis/cadastrar')	
		 *	nesse caso, somente 'imoveis/cadastrar' ficaria sem permissão.
		 *	No UserAccess, a ordem implica.
		 *	
		 *	@param $url Url que será permitida o acesso para o nível atual
		 */
		public function allow($url){
			$this->permissions[$this->now_level][$url] = true;
			return $this;
		}
		
		/**
		 *	Alimento o array de permissões negadas
		 */
		public function deny($url){
			$this->permissions[$this->now_level][$url] = false;
			return $this;
		}
		
		/**
		 *	Efetua o login, baseado nos dados passados pelo usuário
		 *	
		 *	@return bool TRUE em caso de login com sucesso, e FALSE em caso de falha
		 */
		public function login(){
			if(!empty($this->controller->data)):
				//se login ok
				$user = $this->identify();
				if($user):
					setcookie("user_id", $user['id'], $this->expires, '/');
					setcookie("user_password", $user[$this->configs['password']], $this->expires, '/');
					return true;
				//se o login falhar
				else:
//					setcookie("user_id", '', time() - 3600, '/');
//					setcookie("user_password", '', time() - 3600, '/');
					$this->error('authError');
					return false;
				endif;
			endif;
		}
		/**
		 *	Efetuo o logout, excluo o cookies
		 */
		public function logout(){
			setcookie("user_id", '', time() - 3600, '/');
			setcookie("user_password", '', time() - 3600, '/');
			$this->redirect($this->logoutRedirect);
		}	
		
		/**
		 *	Estou logado?
		 */
		public function loggedIn(){
			if(isset($_COOKIE['user_id']) && isset($_COOKIE['user_password'])):
				return true;
			endif;
			return false;
		}
		
		/**
		 *	Define o foco do encadeamento para o nível especificado em $level
		 *	Todas permissões e negações feitas serão acrescentadas nesse nível, até que outro nível seja definido
		 *	
		 *	@param string $level O número/nome do nível que iremos acrescentar permissões/negações
		 *	@return Object $this
		 */
		public function userLevel($level){
			$this->now_level = $level;
			return $this;
		}	
		
		/**
		 *	Trago o foco do encadeamento para o nível mais baixo, ou seja, todos os usuários/visitantes, etc
		 *	
		 *	@return Object $this
		 */
		public function allLevels(){
			$this->userLevel('all');
			return $this;
		}	
		
		/**
		 *	Trago o foco do encadeamento para o nível dos usuários logados
		 *	
		 *	@return Object $this
		 */
		public function allLogged(){
			$this->userLevel('logged');
			return $this;
		}
		
		/**
		 *	Adiciono um login redirect para cada nível de usuário
		 *	
		 *	@return Object $this
		 */
		public function loginRedirect($url){
			$this->loginRedirect[$this->now_level] = $url;
			return $this;
		}
		
		/**
		 *	Seta as configurações padrão
		 *	Formas de uso:
		 *	1)	Com Arrays:	$this->config(array('model'=>'Users', 'username'=>'nome_usuario'));
		 *	2)	Encadeado:	$this->config('model', 'Users')->config('username', 'nome_usuario');
		 *	
		 *	@param mixed $key	Pode receber a chave a ser alterada, ou um array com as configurações
		 *							Se for passado a chave, é necessário informar o parâmetro $value, para
		 *							alterar o valor, ou então $value receberá null. Caso $key seja um array,
		 *							o parâmetro $value não será necessário, pois esse array já deverá conter
		 *							todas as chaves que serão alteradas e seus respectivos valores
		 *	@param string $value O novo valor para a chave informada
		 *	@return Object $this
		 */
		public function config($key, $value=null){
			if(is_array($key)):
				$this->configs = array_merge($this->configs, $key);
			else:
				$this->configs[$key] = $value;
			endif;
			return $this;
		}

		/**
		 *	Salvo o novo model nas configurações.
		 *	
		 */
		public function model($model){
			$this->model = $model;
			return $this;
		}

		/**
		 *	Retorno o objeto model, abstraindo as chamadas a ClassRegistry::init()
		 *	Usa o próprio model já definido em $this->model.
		 */
		public function objModel(){
			return ClassRegistry::init($this->model);
		}
		
		/**
		 *	Retorna a página atual, sem uma possível barra final.
		 */
		public function here(){
			return '/' . trim(Mapper::here(), '/');
			$base = array('prefix', 'controller', 'action');
		/**
			// Essa solução abaixo, é para resolver a questão da ambiguidade, 
			// e remoção de declarações duplas, explicadas no e-mail.
			// Da forma acima, '/login' é diferente de '/users/login',  podendo 
			//obrigar ao desenvolvedor declarar as duas, ou deixar uma delas aberta 
			//a acesso, caso ele não atente.
			// A forma abaixo pega os componentes da url, e gera a url que será 
			//usada pelos outros métodos do component.
			
			foreach($base as $urlComponents):
				if($urlComponents=='action'):
					if($this->controller->params['action']=='index'):
						continue;
					endif;
				endif;
				$buff . = '/' . $this->controller->params[$urlComponents];
			endforeach;
			return '/' . trim($buff, '/');
		*/
		}
		
		/**
		 *	Abstrai a criação das mensagens de erro.
		 */
		public function error($key){
			//integração com o Validation. Passado o erro para uma variável global, e acessível ao Validation.
			//Para exibir os erros, o Validation pegará o objeto UserAccess e o valor da variável $this->errors
			$this->errors = 'wrongData';
			//Envio para a view a variável $authError, pra manter o padrão com o Auth
			$this->controller->set('authError', $this->errors);
		}
		
		/**
		 *	Verifica se a url informada, é a mesma url atual, ou uma url Pai
		 *	Ex.:
		 *		Se eu tiver na página /imoveis/cadastrar, match('/imoveis') retornaria true.
		 */
		public function match($url){
			$url = '/' . trim($url, '/');

			if(substr($this->here(), 0, strlen($url)) == $url):
				return true;
			endif;
			return false;
		}
	}
?>