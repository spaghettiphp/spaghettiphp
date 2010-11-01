<?php
/**
-------------------------------------------- 
  METHODS
---------------------------------------------
  * 
  * getAuthData() - returns auth data if is a authenticate request/false if not authenticate
  * getClientAccepts() - returns a list containing all format supported by client
  * getClientIp() - returns the client ip address
  * getOs() - returns an array containing info about the client operational system
  * getReferer()
  * getUserAgent() returns an array containing info about the client browser
  * getStream() returns the file stream sent by a http request
  * getData() returns the data sent by a http request
  * getMethod() returns the http method used to sent the current request
  * getHeaders() returns all headers from request, or just a specific one
  *
  * isAccepted($type) return true if is a 
  * isOs($os) returns true if is a specific operational system
  * isUserAgent($ua) returns true if is a specific user agent
  * 
  * isGet(), isPost(), isPut(), isDelete() returns true if is a GET, POST, PUT or DELETE request, respectively.
  * isAjax() returns true if is a http xml request
  * isMobile() returns true if a request came from a mobile device
  * isSsl() returns true if is a secure connection
  * isWap() returns true if is a wap request
  *
  *----
  * OUTPUT METHODS
  *----
  * forceDownload() - forces download of the specified file
  * toJson() - convert array into a json format
  * toXml() - convert array into a xml format
  *
-------------------------------------------- 
  CHANGELOG
--------------------------------------------
  * 14/08/2010
  * [m] - toXml agora verifica o conteúdo do valor, e muda a exibição caso esteja
  *     vazio, tiver caracteres especiais, ou for apenas texto simples
  *
  * 24/08/2010
  * [+] - forceDownload() força o download do arquivo especificado
  *
  * 04/09/2010
  * [m] - getRequestData() verifica o tipo do request antes de pegar os dados,
  *     evitando, assim, devolver somente o $_REQUEST
  *
  * 24/10/2010
  * [m] - toXml() verifica se tem atributos na chave e conserta a tag de fechamento
  * [+] - endXmlTag() trata a tag de fechamento do xml
  *
  * 29/10/2010
  * [m] - getRequestData() agora está funcionando corretamente para o método PUT
  * [+] - getRequestHeaders() retorna os cabeçalhos da requisição
  * [+] - getStream() retorna um php stream enviado via http
  * [m] - getRequestData() renomeado para getData()
  * [m] - getRequestMethod() renomeado para getMethod()
  * [m] - getRequestHeaders() renomeado para getHeaders()
  */
class Request{
    public static $userAgents = array(
        'chrome' => 'Chrome', //Chrome
        'firefox' => 'Firefox',
        'ie6' => 'MSIE 6.0',
        'ie7' => 'MSIE 7.0',
        'ie8' => 'MSIE 8.0',
        'ie9' => 'MSIE 9.0',
        'ie'  => 'MSIE', //other IE versions
        'safari' => 'Safari',//it`s not chrome, it is safari
        'operamini' => 'Opera Mini',
        'operamobile'=>'Opera Mobi',
        'opera' => 'Opera',
    );

    public static $desktopOs = array(
        'linux' => 'Linux',
        'macos' => '(Mac)',
        'seven' => 'Windows NT 7.0',
        'vista' => 'Windows NT 6.0',
        'xp' => 'Windows NT 5.1',
    );

    public static $mobileOs = array(
        'android'     => "Android",
        'blackberry' => "BlackBerry",
        'ericsson' => "Sony Ericsson",
        'iphone' => 'Iphone',
        'ipad' => 'Ipad',
        'lg' => "LG",
        'palm' => 'Palm',
        'symbian' => 'Symb',//SymbOS, SymbianOS, Symbian60
        'windowsce' => 'Windows CE',
    );

    public static $accept = array(
        'atom'=> 'application/atom+xml',
        'css' => 'text/css',
        'csv' => 'text/csv',
        'gif' => 'image/gif',
        'html'=> 'text/html',
        'jpg' => 'image/jpeg',
        'js'  => 'application/javascript',
        'json'=> 'application/json',
        'pdf' => 'application/pdf',
        'png' => 'image/png',
        'rss' => 'application/rss+xml',
        'txt' => 'text/plain',
        'yaml'=> 'text/yaml',
        'xhtml' => 'application/xhtml+xml',
        'xml' => 'application/xml',
    );
    
    /**
      * Verifica se o usuário está enviando uma autenticação HTTP
      *
      * @return mixed Retorna um objeto com os dados de autenticação caso
      *     esses dados existam. Os dados estão no seguinte formato
      *         object{user->'nome-do-usuario', password->'senha'}
      *     e retorna FALSE caso não sejam encontrados valores de
      *     autenticação
      */
    public static function getAuthData() {
        if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])):
            return (object) array(
                'user' => $_SERVER['PHP_AUTH_USER'],
                'password' => $_SERVER['PHP_AUTH_PW'],
            );
        endif;
  
        return false;
    }

    /**
      * Processa a informação do HTTP_ACCEPT enviada pelo navegador do
      * requisitante
      * 
      * @return array Retorna um array no seguinte formato
      *   array
      *   (
      *     [0] => application/xml
      *     [1] => application/xhtml+xml
      *     [2] => text/html
      *   )
      */
    public static function getClientAccepts() {
        //return a list of all accepted types
        return preg_split('(;q=[0-9\.\,]+|,)',
                          $_SERVER['HTTP_ACCEPT'], -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
      * Pega o IP do cliente que está acessando a aplicação
      * 
      * @return string IP
      */
    public static function getClientIp() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
      * Pega o Sistema Operacional do cliente que está acessando a aplicação, ou
      * procura no texto informado em $httpUserAgent pelo sistema operacional
      * 
      * @param string $httpUserAgent String HTTP_USER_AGENT. Se não for informada, será
      *     utilizada a informação do cliente que está acessando a aplicação
      * @return array
      */
    public static function getOs($httpUserAgent = null) {
        if(empty($httpUserAgent)):
            $httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
        endif;
        $os = self::$desktopOs + self::$mobileOs;
        preg_match('('.implode('|', $os).')', $httpUserAgent, $out);

        if(!empty($out)):
            return array(array_search($out[0], $os) => $out[0]);
        else:
            return array('unknown' => 'Unknown');
        endif;
    }

    /**
      * Pega o endereço que enviou o usuário para essa página atual
      * 
      * @return string Url
      */
    public static function getReferer() {
        if(isset($_SERVER['HTTP_REFERER'])):
            return $_SERVER['HTTP_REFERER'];
        endif;
        
        return false;
    }

    /**
      * Pega o nome amigável do navegador do cliente usado para acessaar a aplicação,
      * ou, no parâmetro $httpUserAgent, caso este seja informado.
      *
      * @version    1.0
      *         - Initial
      *             1.1 14/08/2010
      *         - Retorna só uma string
      * @param string $httpUserAgent Texto do user_agent
      * @return string
      */
    public static function getUserAgent($httpUserAgent = null) {
        if(empty($httpUserAgent)):
            $httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
        endif;
        
        preg_match('('.implode('|', self::$userAgents).')', $httpUserAgent, $out);

        return reset($out);
    }
    
    /**
      * Pega os dados da requisição.
      * 
      * @version    1.0
      *         - Initial
      *             1.1 04/09/2010
      *         - Retorna $_POST ou $_GET caso seja um desses, e nao só $_REQUEST
      *             1.2 29/10/2010
      *         - PUT agora está funcionando corretamente
      *         - Renomeado para getData()
      *
      * @return array  Dados da requisição
      */
    public static function getData() {
        $requestMethod = self::getMethod();
        
        switch($requestMethod):
            case 'POST':
                return $_POST;
                break;
        
            case 'GET':
                return $_GET;
                break;
        
            case 'PUT':
                /*$fp = fopen('php://input', 'r');
                $output='';
                while($out = fread($fp, 1024)):
                    $output .= $out;
                endwhile;
                //Arquivo enviado via http request/2
                if(self::getRequestHeaders('HTTP_X_FILENAME')):
                    return $output;
                else:
                    parse_str(trim($output, '"'), $return);
                    return $return;
                endif;
                return $output;/**/
                
                parse_str(trim(self::getStream(), '"'), $output);
                
                return $output;
                break;
        
            default:
                return $_REQUEST;
                break;
        endswitch;
        
    }
    
    public function getStream(){
        $fp = fopen('php://input', 'r');
        
        $stream = '';
        
        while($output = fread($fp, 1024)):
            $stream .= $output;
        endwhile;
        
        return $stream;
    }

    /**
      * Pega os cabeçalhos da requisição.
      * 
      * @version    1.0 29/10/2010
      *         - Initial
      *         
      * @param string optional $header O cabeçalho desejado, ou todos os cabeçalhos caso
      *     não seja informado.
      * @return array  Headers da requisição. FALSE caso nada seja encontrado
      */
    public static function getHeaders($header = null) {
        if(empty($header)):
            return $_SERVER;
        else:
            if(isset($_SERVER[$header])):
                return $_SERVER[$header];
            else:
                return false;
            endif;
        endif;
    }
    
    /**
      * Pega o tipo da requisição
      *
      * @version    1.0
      *          - Initial
      *             1.1 29/10/2010
      *          - Renomeado para getMethod()
      * 
      * @return
      */
    public static function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    #
    #   REQUEST TYPE METHODS
    #
    
    /**
      * É uma requisição ajax?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return boolean
      */
    public static function isAjax() {
        return array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
      * É uma requisição do tipo GET?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return boolean
      */
    public static function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
      * É uma requisição do tipo POST?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return boolean
      */
    public static function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
      * É uma requisição do tipo PUT?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return boolean
      */
    public static function isPut() {
        return $_SERVER['REQUEST_METHOD'] === 'PUT';
    }

    /**
      * É uma requisição do tipo DELETE?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return boolean
      */
    public static function isDelete() {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE';
    }

    /**
      * É uma requisição segura?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return boolean
      */
    public static function isSsl() {
        //if is a secure connection
        return isset($_SERVER['HTTPS']);
    }

    /**
      * É uma requisição feita a partir de um cliente mobile?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return boolean
      */
    public static function isMobile() {
        foreach(self::$mobileOs as $slug => $name):
            if(strpos($_SERVER['HTTP_USER_AGENT'], $name) !== false):
                return $slug;
            endif;
        endforeach;
        
        return false;
    }

    /**
      * É uma requisição a partir de um navegador WAP?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return boolean
      */
    public static function isWap() {
        //return preg_match("/wap\.|\.wap/i", );
        if(stripos($_SERVER['HTTP_ACCEPT'], 'wap') !== false):
            return true;
        endif;
        
        return false;
    }
    
    #
    #   IS INFORMATION METHODS
    #
    
    /**
      * Verifica se o tipo passado em $type é aceito pelo usuário requisitante.
      * 
      * @version    1.0
      *          - Initial
      * 
      * @param string $type Tipo a ser verificado. $type pode ser no formato 'xml'
      *     por exemplo, ou no formato application/xml.
      * @return bool TRUE caso seja aceito o tipo pesquisado, FALSE caso não seja
      */
    public static function isAccepted($type) {
        $acceptList = self::getClientAccepts();
        
        //if $type is accepted by requester
        return in_array($type, $acceptList) ||
                array_key_exists($type, self::$accept) &&
                in_array(self::$accept[$type], $acceptList);
    }

    /**
      * Verifica se o requisitante está usando um sistema operacional especificado
      * em $os
      * 
      * @version    1.0
      *          - Initial
      * 
      * @param string $os Sistema operacional a ser verificado
      * @param string $stringHttpUserAgent Optional Caso não seja informado,
      *     será usado  a informação contida em $_SERVER['HTTP_USER_AGENT']
      *     do usuário que requisitou a página
      * @return bool TRUE caso seja o sistema operacional procurado, e FALSE
      *     caso não seja.
      */
    public static function isOs($os, $stringHttpUserAgent = null) {
        
        //if is a specific operation system
        return key(self::getOs($stringHttpUserAgent)) === $os;
    }
    
    /**
      * Diz se o user-agent é mesmo o que estamos procurando.
      * 
      * @version    1.0
      *          - Initial
      * 
      * Exemplo:
      * //Retorna true or false caso o usuário esteja usando o IE8
      * isUserAgent('ie8')
      * 
      * //Retorna true or false caso a string informada no segundo parâmetro
      * //seja referente ao navegador firefox
      * isUserAgent('firefox', 'Mozilla/5.0 (Windows; U) Gecko/20100401 Firefox/3.6.3')
      *     
      * @param string $specificUserAgent A specific user-agent for comparison
      * @param string $userAgent Forçar comparação com esse user-agent.
      *     Caso não seja informado, será usado o próprio user-agent que
      *     está sendo usado para acesso à página
      * @return boolean True se for o user agent procurado, e false caso não seja
      */
    public static function isUserAgent($specificUserAgent, $stringHttpUserAgent = null) {
        if(empty($stringHttpUserAgent)):
            $stringHttpUserAgent = $_SERVER['HTTP_USER_AGENT'];
        endif;
        //if is a specific user agent?
        if(strpos($stringHttpUserAgent, self::$userAgents[$specificUserAgent]) !== false):
            return self::$userAgents[$specificUserAgent];
        endif;
        return false;
    }

    /**
      * É um acesso local?
      * 
      * @version    1.0
      *          - Initial
      * 
      * @return bool TRUE se o requisitante estiver usando uma rede local
      */
    public static function isLocal(){
        return $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' || strpos('192.168.', $_SERVER['REMOTE_ADDR']) === 0;
    }

    /**
      * Verifica se o Ip informado está dentro de um intervalo
      * 
      * @version    1.0
      *          - Initial
      *
      * @param string $initialRange IP inicial
      * @param string $endRange IP final
      * @param string optional $userIp IP a ser verificado. Será o próprio IP da requisição,
      *     caso não seja informado.
      * @return boolean TRUE caso esteja dentro do intervalo, e FALSE caso não esteja.
      */
    public static function inIpRange($initialRange, $endRange, $userIp = null){
        if(empty($userIp)):
            $userIp = self::getClientIp();
        endif;

        $userIp = self::ipToLong($userIp);
        $initialRange = self::ipToLong($initialRange);
        $endRange = self::ipToLong($endRange);

        return ($userIp >= $initialRange) && ($userIp <= $endRange);
    }
  
    #
    #    RETURN METHODS
    #
    
    /**
      * Transforma o valor informado para JSON
      * 
      * @version    1.0
      *          - Initial
      * 
      * @param mixed $input Valor inicial
      * 
      * @return
      */
    public static function toJson($input) {
        return json_encode($input);
    }

    /**
      * Devolve um array como XML, levando em consideração chaves numéricas
      *
      * @version
      *     1.0 - 28/05/2010
      *     - Versão final contendo várias melhorias.
      *     1.1 - 14/08/2010
      *     - Adicionada a verificação do tipo do conteúdo, pra saber está vazio, ou se
      *     contém caracteres especiais, etc.
      *     1.2 - 24/10/2010
      *     - Verifica se há atributos na tag de abertura, e evita que eles sejam exibidos
      *     na tag de fechamento.
      * @param array $array Array de dados
      * @param string $initialKey Chave inicial que irá encobrir o próximo nó do Xml
      * @return Xml
      */
    public static function toXml($array = array(), $initialKey = null){
        header("Content-Type: application/xml; Charset=UTF-8");

        $output = "";
        $end = "";
        
        if(!empty($initialKey)):
            //A próxima chave do conjunto atual não é numérica
            $array_keys = array_keys($array);
            
            if(!is_numeric(reset($array_keys))):
                $output .= "<{$initialKey}>\n";
                //$end = "\n</{$initialKey}>";
                $end = self::endXmlTag($initialKey);
            endif;
        endif;

        foreach($array as $row => $val):
            if(is_numeric($row)):
                $row = $initialKey;
            endif;
            
            if(is_array($val)):
                $output .= self::toXml($val, $row);
            else:
                if(empty($val)):
                    $output .= "<{$row} />\n";
                elseif($val != $newVal = htmlspecialchars($val)):
                    $output .= "<{$row}><![CDATA[" . $newVal."]]>".self::endXmlTag($row)."\n";
                else:
                    $output .= "<{$row}>{$val}". self::endXmlTag($row) ."\n";
                endif;
            endif;
        endforeach;
        
        $output .= $end;
        return $output;
    }

    /**
      * Transforma o IP informado em um LONG
      * 
      * @version    1.0
      *          - Initial
      * 
      * @param string $ip IP a ser transformado
      * @return long
      */
    public static function ipToLong($ip){
        return ip2long($ip);
    }
    /** 
      * Transforma o long informado em um IP
      * 
      * @version    1.0
      *          - Initial
      * 
      * @param long $long Long a ser transformado para IP
      * @return string
      */
    public static function longToIp($long){
        return long2ip($long);
    }
    
    
    /**
      * Força o download do arquivo
      * 
      * @version    1.0
      *          - Initial
      * 
      * @param $file O nome do arquivo, relativo a /SPAGHETTI_ROOT
      */
    public static function forceDownload($file){
        $file = Filesystem::path($file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));

        ob_clean();
        flush();
        readfile($file);
    }
    
    private static function endXmlTag($key){
        return "</".preg_replace('(^([^ ]+) (.*)$)', '\1', $key).">";
    }
}
?>