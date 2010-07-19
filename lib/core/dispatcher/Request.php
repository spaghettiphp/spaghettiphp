<?php
/**
  * arrayToJson() - convert array into a json format
  * arrayToXml() - todo
  *
  * getAuthData() - returns auth data if is a authenticate request/false if not authenticate
  * getClientAccepts() - returns a list containing all format supported by client
  * getClientIp() - returns the client ip address
  * getOs() - returns an array containing info about the client operational system
  * getReferer()
  * getUserAgent() returns an array containing info about the client browser
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
  */
class Request extends Object{
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
        'android'     => "Google OS",
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
      * 
      * 
      * @return
      */
    public static function getClientIp() {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
      * 
      * 
      * @return
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
      * 
      * 
      * @return
      */
    public static function getReferer() {
        if(isset($_SERVER['HTTP_REFERER'])):
            return $_SERVER['HTTP_REFERER'];
        endif;
        
        return false;
    }

    /**
      * 
      * 
      * @return
      */
    public static function getUserAgent($httpUserAgent = null) {
        if(empty($httpUserAgent)):
            $httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
        endif;
        
        preg_match('('.implode('|', self::$userAgents).')', $httpUserAgent, $out);
        
        return $out;
    }
    
    /**
      * 
      * 
      * @return
      */
    public static function getRequestData() {
        return $_REQUEST;
    }
    
    /**
      * 
      * 
      * @return
      */
    public static function getRequestMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
        request type methods
     **/
    
    /**
      * 
      * 
      * @return
      */
    public static function isAjax() {
        return array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
      * 
      * 
      * @return
      */
    public static function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
      * 
      * 
      * @return
      */
    public static function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
      * 
      * 
      * @return
      */
    public static function isPut() {
        return $_SERVER['REQUEST_METHOD'] === 'PUT';
    }

    /**
      * 
      * 
      * @return
      */
    public static function isDelete() {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE';
    }

    /**
      * 
      * 
      * @return
      */
    public static function isSsl() {
        //if is a secure connection
        return isset($_SERVER['HTTPS']);
    }

    /**
      * 
      * 
      * @return
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
      * 
      * 
      * @return
      */
    public static function isWap() {
        //return preg_match("/wap\.|\.wap/i", );
        if(stripos($_SERVER['HTTP_ACCEPT'], 'wap') !== false):
            return true;
        endif;
        
        return false;
    }
    
    /**
        Is information methods
     **/
    
    /**
      * Verifica se o tipo passado em $type é aceito pelo usuário requisitante.
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
      * @return bool TRUE se o requisitante estiver usando uma rede local
      */
    public static function isLocal(){
        return $_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' || strpos('192.168.', $_SERVER['REMOTE_ADDR']) === 0;
    }

    /**
      * 
      * 
      * @return
      */
    public static function inIpRange($initialRange, $endRange, $userIp = null){
        if(empty($userIp)):
            $userIp = self::getClientIp();
        endif;

        $userIp = self::ipToLong($userIp);
        $initialRange = self::ipToLong($initialRange);
        $endRange = self::ipToLong($endRange);
//        pr($userIp);
//        pr($initialRange);
//        pr($endRange);

        return ($userIp >= $initialRange) && ($userIp <= $endRange);
    }
  
    /**
        return methods
     **/
    
    /**
      * 
      * 
      * @return
      */
    public static function toJson(array $input) {
        return json_encode($input);
    }

    /**
      * Devolve um array como XML, levando em consideração chaves numéricas
      *
      * @version
      *     1.0 - 28/05/2010
      *     - Versão final contendo várias melhorias.
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
            if(!is_numeric(reset(array_keys($array)))):
                $output .= "<{$initialKey}>\n";
                $end = "\n</{$initialKey}>";
            endif;
        endif;

        foreach($array as $row => $val):
            if(is_numeric($row)):
                $row = $initialKey;
            endif;
            
            if(is_array($val)):
                $output .= self::toXml($val, $row);
            else:
                $output .= "<{$row}>{$val}</{$row}>\n";
            endif;
        endforeach;
        
        $output .= $end;
        return $output;
    }

    /**
      * 
      * 
      * @return
      */
    public static function ipToLong($ip){
        return ip2long($ip);
    }
    /**
      * 
      * 
      * @return
      */
    public static function longToIp($long){
        return long2ip($long);
    }
}
?>