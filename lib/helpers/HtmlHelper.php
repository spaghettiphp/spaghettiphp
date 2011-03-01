<?php

class HtmlHelper extends Helper {
    public $scriptsForLayout = '';
    public $stylesForLayout = '';
    protected $view;

    public function __construct($view) {
        parent::__construct($view);
    }

    public function openTag($tag, $attr = array(), $empty = false) {
        $html = '<' . $tag;
        $attr = $this->attr($attr);
        if(!empty($attr)) {
            $html .= ' ' . $attr;
        }
        $html .= ($empty ? ' /' : '') . '>';

        return $html;
    }

    public function closeTag($tag) {
        return '</' . $tag . '>';
    }

    public function tag($tag, $content = '', $attr = array(), $empty = false) {
        $html = $this->openTag($tag, $attr, $empty);
        if(!$empty) {
            $html .= $content . $this->closeTag($tag);
        }

        return $html;
    }

    public function attr($attr) {
        $attributes = array();
        foreach($attr as $name => $value) {
            if($value === true) {
                $value = $name;
            }
            elseif($value === false) {
                continue;
            }
            $attributes []= $name . '="' . $value . '"';
        }

        return join(' ', $attributes);
    }

    public function link($text, $url = null, $attr = array(), $full = false) {
        if(is_null($url)) {
            $url = $text;
        }

        $attr['href'] = Mapper::url($url, $full);

        return $this->tag('a', $text, $attr);
    }

    public function image($src, $attr = array()) {
        $attr += array(
            'alt' => '',
            'title' => array_key_exists('alt', $attr) ? $attr['alt'] : ''
        );

        $attr['src'] = $this->assets->image($src);

        return $this->tag('img', null, $attr, true);
    }

    public function imagelink($src, $url, $img_attr = array(), $attr = array(), $full = false) {
        $image = $this->image($src, $img_attr);
        return $this->link($image, $url, $attr, $full);
    }

    public function stylesheet() {
        list($href, $inline) = $this->normalizeArgs(func_get_args());

        $output = '';
        foreach($href as $tag) {
            $attr = array(
                'href' => $this->assets->stylesheet($tag),
                'rel' => 'stylesheet',
                'type' => 'text/css'
            );
            $output .= $this->tag('link', null, $attr, true);
        }

        if($inline) {
            return $output;
        }
        else {
            $this->stylesForLayout .= $output;
        }
    }

    public function script() {
        list($src, $inline) = $this->normalizeArgs(func_get_args());

        $output = '';
        foreach($src as $tag) {
            $attr = array(
                'src' => $this->assets->script($tag)
            );
            $output .= $this->tag('script', null, $attr);
        }

        if($inline) {
            return $output;
        }
        else {
            $this->scriptsForLayout .= $output;
        }
    }

    public function nestedList($list, $attr = array(), $type = 'ul') {
        $content = '';
        foreach($list as $k => $li) {
            if(is_array($li)) {
                $li = $this->nestedList($li, array(), $type);
                if(!is_numeric($k)) {
                    $li = $k . $li;
                }
            }
            $content .= $this->tag('li', $li) . PHP_EOL;
        }

        return $this->tag($type, $content, $attr);
    }

    public function charset($charset = null) {
        if(is_null($charset)) {
            $charset = Config::read('App.encoding');
        }

        $attr = array(
            'http-equiv' => 'Content-type',
            'content' => 'text/html; charset=' . $charset
        );

        return $this->tag('meta', null, $attr, true);
    }

    protected function normalizeArgs($args) {
        $bool = true;

        if(is_array($args[0])) {
            list($args, $bool) = array(array_shift($args), array_shift($args));

            if(is_null($bool)) {
                $bool = true;
            }
        }
        else if(is_bool(end($args))) {
            $bool = array_pop($args);
        }

        return array($args, $bool);
    }
}