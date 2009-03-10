<?php
/**
 *  Short Description
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2008-2009, Spaghetti* Framework (http://spaghettiphp.org/)
 *
 */

class ImageComponent extends Object {
    private $imageTypes = array("gif", "jpeg", "jpeg" => "jpg", "png");
    private $params = array(
        "height" => 0,
        "width" => 0,
        "x" => 0,
        "y" => 0,
        "constrain" => false,
        "quality" => 80,
        "filename" => false
    );
    public function resize($filename = null, $params = array()) {
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
            $filename = $params["filename"];
        endif;
        
        return $fnOutput($output, $filename, $params["quality"], PNG_ALL_FILTERS);
    }
    public function convert($filename = null, $params = array()) {
        $resize = $this->resize($filename, $params);
        if(!$params["keep"]):
            unlink($filename);
        endif;
        return $resize;
    }
    public function ext($filename = "") {
        $ext = trim(substr($filename, strrpos($filename, ".") + 1, strlen($filename)));
        if(in_array($ext, $this->imageTypes)):
            $key = array_search($ext, $this->imageTypes);
            if(is_string($key)):
                $ext = $key;
            endif;
            return $ext;
        endif;
        return false;
    }
}

?>