<?php
/**
  * Classe de requisições HTTP
  * @version 0.06 - 02/05/2010
  *
  *  Exemplos de uso:
  *    - Postando no Twitter
  *    Http::auth('user', 'senha');
  *    echo Http::post('http://api.twitter.com/1/statuses/update.json',
  *                array('status' => 'Testando o Core.Http'));
  *
  *    - Encurtando uma url com o bit.ly
  *    $params = array(
  *      'login'	  => 'klawdyo', //Registro em http://bit.ly/account/register
  *      'apiKey'	  => 'R_2531c63fdc13b904d94fc084', //http://bit.ly/account/your_api_key
  *      'longUrl'  => 'http://google.com'
  *    );
  *    echo Http::post('http://api.bit.ly/v3/shorten', $params);
  *
  *  
  */
class Http extends Object {
    /*
     *  Lista de retornos possíveis.
     *  Alguns servidores verificam esse cabeçalho para decidir qual o formato que os da-
     *  dos serão retornados
     */
    public $accept = array(
        'atom' => 'application/atom+xml',
        'css' => 'text/css',
        'csv' => 'text/csv',
        'gif' => 'image/gif',
        'html' => 'text/html',
        'jpg' => 'image/jpeg',
        'js'  => 'application/javascript',
        'json' => 'application/json',
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'rss' => 'application/rss+xml',
        'txt' => 'text/plain',
        'yaml' => 'text/yaml',
        'xml' => 'application/xml',
    );
    
    /*
     *   O nome de usuário
     */
    public $user;
    /*
     *   A senha do usuário
     */
    public $password;
    /*
     *   O resultado da requisição
     */
    public $result;
    /*
     *   As opções do cURL
     */
    public $curlOptions = array();
    /*
     *   Código HTTP do status da requisição. Ex.: 403
     */
    public $statusCode;
    /*
     *   Tipo do retorno. Ex.: "application/json; charset=utf8"
     */
    public $contentType;
    /*
     *   Informações sobre a requisição
     */
    public $info;
    /*
     *   Instância do Objeto
     */
    protected static $instance;

    /**
      *    Retorna uma instância única do objeto
      *    
      *    @return object
      */
    public static function instance() {
        if(!isset(self::$instance)):
            $c = __CLASS__;
            self::$instance = new $c;
        endif;
        return self::$instance;
    }     

   /**
     *   Ativa autenticação para a próxima requisição.
     *   Para desativar a autenticação que foi ativada para a requisição anterior, use
     *   Http::auth(false);
     *   
     *   @param string $user  O nome de usuário
     *   @param string $password A senha do usuário
     *   @return void
     */
   public static function auth($user, $password = null, $basic = true) {
      $self = self::instance();

      if($user === false):
         $self->curlOptions[CURLOPT_HTTPAUTH] = false;
         return false;
      endif;
      //Dados de autenticação
      $self->user = $user;
      $self->password = $password;
      //Definindo o tipo da autenticação
      if($basic):
          $self->curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
      else:
          $self->curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
      endif;
      //Criando usuário e senha no formato do curl
      $self->curlOptions[CURLOPT_USERPWD] = $self->user . ':' . $self->password;
   }
   
   /**
     *   Centraliza o envio das requisições http
     *   
     *   @param string $url  A url  que receberá a requisição
     *   @param string $method O tipo de requisição. Ex.: POST, GET, etc
     *   @return mixed O resultado da requisição
     */
    public static function request($url, $method) {
        $self = self::instance();
        //Tipos de requisições e seus correspondentes no cURL
        $methods = array(
            'GET' => CURLOPT_HTTPGET,
            'POST'=> CURLOPT_POST,
        );
    //Existe no array $methods
    if(array_key_exists($method, $methods)):
        $self->curlOptions[$methods[$method]] = true;
    //Não existe, envia uma requisição customizada
    else:
        $self->curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
    endif;
    //Inicia a sessão curl
    $ch = curl_init($url);
    //Traz o resultado como string, ao invés de jogá-lo diretamente na saída
    $self->curlOptions[CURLOPT_RETURNTRANSFER] = true;
    //Coloca os arrays como opções do curl
    curl_setopt_array($ch, $self->curlOptions);
    //Executa
    $self->result = curl_exec($ch);
    //Pega o código de cabeçalho retornado
    $self->statusCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //Tipo do conteúdo retornado.
    $self->contentType= curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    //info
    $self->info = curl_getinfo($ch);
    //Fecha a sessão curl
    curl_close($ch);
    //Retorna
    return $self->result;
   }

    /**
      *   Envia uma requisição do tipo GET
      *
      *   @param  string $url   A url do servidor
      *   @param  array  $params Parâmetros adicionais para serem enviados. Os parâmetros
      *      aqui infomados serão colocados na query string
      *   @return mixed  Os dados retornados pelo servidor
      */
    public static function get($url, $params = array()) {
        $self = self::instance();

        if(!empty($params)):
            $url .= '?' . http_build_query($params);
        endif;
        
        return $self->request($url, 'GET');      
    }
    /**
      *   Envia uma requisição do tipo POST
      *
      *   @param  string $url   A url do servidor
      *   @param  mixed  $params Parâmetros adicionais para serem enviados, no formato de
      *     array associativo, ou no formato de querystring.
      *   @return mixed  Os dados retornados pelo servidor
      */
    public static function post($url, $params = null) {
        $self = self::instance();

        if(is_array($params)):
            $params = http_build_query($params);
        endif;
        $self->curlOptions[CURLOPT_POSTFIELDS] = $params;

        return self::request($url, 'POST');
   }
   
    /**
      *   Envia uma requisição do tipo PUT
      *
      *   @param  string $url   A url do servidor
      *   @param  mixed  $params Parâmetros adicionais para serem enviados, no formato de
      *     array associativo, ou no formato de querystring.
      *   @return mixed  Os dados retornados pelo servidor
      */
    public static function put($url, $params = null) {
        $self = self::instance();
        
        if(is_array($params)):
            $params = http_build_query($params);
        endif;
        $self->curlOptions[CURLOPT_POSTFIELDS] = $params;
            
        return self::request($url, 'PUT');
    }

    /**
      *   Envia uma requisição do tipo DELETE
      *   O método delete não recebe parâmetros. Tudo deve ser passado diretamente
      *   na url. Caso $params seja informado, será transformado em uma querystring
      *
      *   @param string $url A url do servidor
      *   @param array  $parrams Os parâmetros
      */
    public static function delete($url, $params = array()) {
        if(!empty($params)):
            $url .= '?' . http_build_query($params);
        endif;
        return self::request($url, 'DELETE');
    }

    /**
      * Define um tipo de dados de retorno específico.
      * Alguns servidores usam esse cabeçalho para retornar os dados no formato desejado.
      *
      * @param string $accept Tipo aceito. Se $accept for uma chave do array self::$accept
      *     só é necessário passar essa chave. Se não for, então espera-se que $accpet seja
      *     um cabeçalho mime-type no formato "application/json".
      * @return string $accept
      */
    public static function accept($accept) {
        $self = self::instance();
        if(array_key_exists($accept, $self->accept)):
            $accept = $self->accept[$accept];
        endif;
        $self->curlOptions[CURLOPT_HTTPHEADER][] = "Accept: {$accept}";
        return $self->accept;
    }

    /**
      * Define o tempo máximo de execução da conexão, em segundos.
      *
      * @param  integer $timeout Tempo máximo de execução em segundos
      * @return integer $timeout 
      */
    public static function timeout($timeout){
        $self = self::instance();
        return $self->curlOptions[CURLOPT_CONNECTTIMEOUT] = $timeout;
    }

    /**
     * Retorna o resultado da requisição
     * 
     * @return string
     */
    public static function result(){
        $self = self::instance();
        return $self->result;
    }

    /**
      * Determina se a requisição foi feita com sucesso.
      * 
      * @return bool
      */
    public static function isSuccess() {
        $self = self::instance();
        return ($self->statusCode >= 200 && $self->statusCode < 300);
    }

    /**
      * Pega o código do status HTTP da requisição
      * 
      * @return int
      */
    public static function getStatusCode() {
        $self = self::instance();
        return $self->statusCode;
    }

    /**
      * Pega o tipo do retorno da requisição
      * 
      * @return string Tipo do retorno, no formato "application/json; charset=utf8"
      */
    public static function getContentType() {
        $self = self::instance();
        return $self->contentType;
    }
    
    /**
      * Pega as informações sobre a requisição
      * 
      * @return array contendo as informações sobre a requisição
      */
    public static function getInfo() {
        $self = self::instance();
        return $self->info;
    }
    
    /**
      * Adiciona opções à execução do cURL.
      *
      * @param array $options    As opções que serão adicionadas às existentes.
      */
     public static function options($options = array()) {
        $self = self::instance();
        $self->curlOptions = array_merge($self->curlOptions, $options);
        return $self->curlOptions;
     }
    
    /**
      * Limpa as opções previamente criadas
      *
      * @return void
      */
    public static function clear() {
        $self = self::instance();
        $self->curlOptions = array();
        $self->accept = null;
        $self->contentType = null;
        $self->statusCode = null;
        $self->password = null;
        $self->user = null;
        $self->result = null;
    }
}
?>