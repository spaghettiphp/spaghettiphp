<?php
/**
 *  ImageComponent provê funcionalidades para a manipulação de imagens, como corte,
 *  redimensionamento, conversão entre formatos e geração de thumbnails.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class ImageComponent extends Component {
    /**
     *  Tipos de imagens suportados.
     */
    private $imageTypes = array("gif", "jpeg", "jpeg" => "jpg", "png");
    /**
     *  Parâmetros padrão para os métodos do componente.
     */
    private $params = array(
        "height" => 0,
        "width" => 0,
        "x" => 0,
        "y" => 0,
        "constrain" => false,
        "quality" => 80,
        "filename" => false
    );
    /**
     *  Redimensiona uma imagem.
     *
     *  @param string $filename Imagem a ser manipulada
     *  @param array $params Parâmetros para a manipulação da imagem
     *  @return mixed Imagem gerada pelo componente
     */
    public function resize($filename = null, $params = array()) {
        $filename = $this->filePath($filename);
        $params = array_merge($this->params, $params);
        $inputExt = $this->ext($filename);
        $outputExt = $this->ext($params["filename"] ? $params["filename"] : $filename);
        $fnInput = "imagecreatefrom{$inputExt}";
        $fnOutput = "image{$outputExt}";
        list($width, $height) = getimagesize($filename);

        if($params["height"] == 0 && $params["width"] == 0):
            $params["height"] = $height;
            $params["width"] = $width;
        endif;

        if($params["constrain"]):
            $ratio = $width > $height ? $width / $params["width"] : $height / $params["height"];
            $params["height"] = $height / $ratio;
            $params["width"] = $width / $ratio;
        endif;
        
        $input = $fnInput($filename);
        $output = imagecreatetruecolor($params["width"], $params["height"]);
        imagecopyresampled($output, $input, 0, 0, $params["x"], $params["y"], $params["width"], $params["height"], $width, $height);
        
        if($params["filename"]):
            $filename = $this->filePath($params["filename"]);
        endif;
        
        return $fnOutput($output, $filename, $params["quality"], PNG_ALL_FILTERS);
    }
    /**
     *  Converte uma imagem de um formato para outro.
     *
     *  @param string $filename Imagem a ser convertida
     *  @param array $params Parâmetros para a conversão da imagem
     *  @return mixed Imagem convertida
     */
    public function convert($filename = null, $params = array()) {
        $resize = $this->resize($filename, $params);
        if(!$params["keep"]):
            unlink($this->filePath($filename));
        endif;
        return $resize;
    }
    /**
     *  Retorna a extensão do arquivo de uma imagem.
     *
     *  @param string $filename Nome da imagem
     *  @return string Extensão do arquivo, falso caso não seja uma imagem válida.
     */
    public function ext($filename = "") {
        $ext = strtolower(trim(substr($filename, strrpos($filename, ".") + 1, strlen($filename))));
        if(in_array($ext, $this->imageTypes)):
            $key = array_search($ext, $this->imageTypes);
            if(is_string($key)):
                $ext = $key;
            endif;
            return $ext;
        endif;
        return false;
    }
    /**
     *  Retorna o caminho de um arquivo a partir de /app.
     *
     *  @param string $filename Nome do arquivo
     *  @return string Caminho do arquivo
     */
    public function filePath($filename = "") {
        return APP . "/webroot/" . $filename;
    }
}

?>