<?php

require_once 'lib/core/filesystem/Filesystem.php';

class ImageResize {
    protected $destiny = array(
        'constrain' => false,
        'height' => 0,
        'quality' => 80,
        'resize' => false,
        'width' => 0,
        'x' => 0,
        'y' => 0
    );
    protected $source = array(
        'x' => 0,
        'y' => 0
    );
    
    public function resize($filename, $destiny = array()) {
        $destiny += $this->destiny;
        $size = $this->size($filename);
        extract($size);
        if($destiny['constrain']):
            if(
                $destiny['width'] && ($width > $height || !$destiny['height'])
            ):
                $ratio = $destiny['width'] / $width;
                $destiny['height'] = floor($height * $ratio);
            elseif(
                $destiny['height'] && ($width < $height || !$destiny['width'])
            ):
                $ratio = $destiny['height'] / $height;
                $destiny['width'] = floor($width * $ratio);
            endif;
        endif;
        
        return $this->createImage($filename, $size + $this->source, $destiny);
    }
    public function scale($filename, $destiny = array()) {
        $destiny += $this->destiny;
        $size = $this->size($filename);
        extract($size);
        $destiny['width'] = $width * ($destiny['scale'] / 100);
        $destiny['height'] = $height * ($destiny['scale'] / 100);

        return $this->createImage($filename, $size + $this->source, $destiny);
    }
    public function crop($filename, $destiny = array()) {
        $destiny += $this->destiny;
        $size = $this->size($filename);
        $source = $this->cropSource($size, $destiny);
        
        return $this->createImage($filename, $source, $destiny);
    }
    public function size($filename) {
        $filename = Filesystem::path('public/' . $filename);
        
        $size = getimagesize($filename);
        return array(
            'width' => $size[0],
            'height' => $size[1],
            'type' => $size[2]
        );
    }
    public function imageType($filename) {
        $ext = strtolower(substr($filename, strrpos($filename, '.') + 1));
        switch($ext):
            case 'jpeg':
            case 'jpg':
                return 'jpeg';
            case 'gif':
                return 'gif';
            case 'png':
                return 'png';
            default:
                return false;
        endswitch;
    }
    protected function createImage($filename, $source, $destiny) {
        $input_type = image_type_to_extension($source['type'], false);
        $input_function = 'imagecreatefrom' . $input_type;
        
        if(!isset($destiny['filename'])):
            $destiny['filename'] = $filename;
        endif;
        $output_type = $this->imageType($destiny['filename']);
        $output_function = 'image' . $output_type;
        
        $filename = Filesystem::path('public/' . $filename);
        $destiny['filename'] = Filesystem('public/' . $destiny['filename']);
        
        $input = $input_function($filename);
        $output = imagecreatetruecolor($destiny['width'], $destiny['height']);
        imagecopyresampled(
            $output, $input,
            $destiny['x'], $destiny['y'],
            $source['x'], $source['y'],
            $destiny['width'], $destiny['height'],
            $source['width'], $source['height']
        );
        imagedestroy($input);

        // @todo check for PNG quality
        $output_image = $output_function($output, $destiny['filename'], $destiny['quality'], PNG_ALL_FILTERS);
        imagedestroy($output);
        
        return $output_image;
    }
    protected function cropSource($source, $destiny) {
        extract($source);
        $source['width'] = $destiny['width'];
        $source['height'] = $destiny['height'];
        if($destiny['resize']):
            if($width > $height && floor($height * $destiny['width'] / $destiny['height']) < $width):
                $source['height'] = $height;
                $source['width'] = floor($height * $destiny['width'] / $destiny['height']);
            else:
                $source['width'] = $width;
                $source['height'] = floor($width * $destiny['height'] / $destiny['width']);
            endif;
        endif;
        $source['x'] = floor(($width - $source['width']) / 2);
        $source['y'] = floor(($height - $source['height']) / 2);
        
        return $source;
    }
}