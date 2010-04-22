<?php

class HtmlHelper extends Helper {
    protected $view;
    
    public function __construct($view) {
        parent::__construct($view);
        $this->view->stylesForLayout = '';
        $this->view->scriptsForLayout = '';
    }
    public function openTag($tag, $attr = array(), $empty = false) {
        $html = '<' . $tag;
        $attr = $this->attr($attr);
        if(!empty($attr)):
            $html .= ' ' . $attr;
        endif;
        $html .= ($empty ? ' /' : '') . '>';
        
        return $html;
    }
    public function closeTag($tag) {
        return '</' . $tag . '>';
    }
    public function tag($tag, $content = '', $attr = array(), $empty = false) {
        $html = $this->openTag($tag, $attr, $empty);
        if(!$empty):
            $html .= $content . $this->closeTag($tag);
        endif;
        
        return $html;
    }
    public function attr($attr) {
        $attributes = array();
        foreach($attr as $name => $value):
            if($value === true):
                $value = $name;
            elseif($value === false):
                continue;
            endif;
            $attributes []= $name . '="' . $value . '"';
        endforeach;
        
        return join(' ', $attributes);
    }
    public function link($text, $url = null, $attr = array(), $full = false) {
        if(is_null($url)):
            $url = $text;
        endif;
        
        $attr['href'] = Mapper::url($url, $full);
        
        return $this->tag('a', $text, $attr);
    }
    public function image($src, $attr = array()) {
        $attr += array(
            'alt' => '',
            'title' => isset($attr['alt']) ? $attr['alt'] : ''
        );

        $attr['src'] = $this->assets->image($src);

        return $this->tag('img', null, $attr, true);
    }
    public function imagelink($src, $url, $img_attr = array(), $attr = array(), $full = false) {
        $image = $this->image($src, $img_attr);
        return $this->link($image, $url, $attr, $full);
    }
    public function stylesheet($href, $attr = array(), $inline = true) {
        if(is_array($href)):
            $output = '';
            foreach($href as $tag):
                $output .= $this->stylesheet($tag, $attr, true) . PHP_EOL;
            endforeach;
        else:
            $attr += array(
                'href' => $this->assets->stylesheet($href),
                'rel' => 'stylesheet',
                'type' => 'text/css'
            );
            $output = $this->tag('link', null, $attr, true);
        endif;
        
        if($inline):
            return $output;
        else:
            $this->view->stylesForLayout .= $output;
            return null;
        endif;
    }
    public function script($src, $attr = array(), $inline = true) {
        if(is_array($src)):
            $output = '';
            foreach($src as $tag):
                $output .= $this->script($tag, $attr, true) . PHP_EOL;
            endforeach;
        else:
            $attr += array(
                'src' => $this->assets->script($src),
                'type' => 'text/javascript'
            );
            $output = $this->tag('script', null, $attr);
        endif;
        
        if($inline):
            return $output;
        else:
            $this->view->scriptsForLayout .= $output;
            return null;
        endif;
    }
    public function nestedList($list, $attr = array(), $type = 'ul') {
        $content = '';
        foreach($list as $k => $li):
            if(is_array($li)):
                $li = $this->nestedList($li, array(), $type);
                if(!is_numeric($k)):
                    $li = $k . $li;
                endif;
            endif;
            $content .= $this->tag('li', $li) . PHP_EOL;
        endforeach;
        
        return $this->tag($type, $content, $attr);
    }
    public function div($content, $attr = array()) {
        if(!is_array($attr)):
            $attr = array('class' => $attr);
        endif;
        
        return $this->tag('div', $content, $attr);
    }
    public function charset($charset = null) {
        if(is_null($charset)):
            $charset = Config::read('App.encoding');
        endif;
        
        $attr = array(
            'http-equiv' => 'Content-type',
            'content' => 'text/html; charset=' . $charset
        );
        
        return $this->tag('meta', null, $attr, true);
    }
}