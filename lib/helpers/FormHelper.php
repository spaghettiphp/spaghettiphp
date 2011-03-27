<?php

require_once 'lib/core/security/Sanitize.php';

class FormHelper extends Helper {
    public function create($action = null, $options = array()) {
        $options += array(
            'method' => 'post',
            'action' => Mapper::url($action)
        );

        if($options['method'] == 'file') {
            $options['method'] = 'post';
            $options['enctype'] = 'multipart/form-data';
        }

        return $this->html->openTag('form', $options);
    }

    public function close($submit = null, $attributes = array()) {
        $form = $this->html->closeTag('form');

        if(!is_null($submit)) {
            $form = $this->submit($submit, $attributes) . $form;
        }

        return $form;
    }

    public function submit($text, $attributes = array()) {
        $attributes += array(
            'type' => 'submit',
            'tag' => 'button'
        );

        switch(array_unset($attributes, 'tag')) {
            case 'image':
                $attributes['alt'] = $text;
                $attributes['type'] = 'image';
                $attributes['src'] = $this->assets->image($attributes['src']);
            case 'input':
                $attributes['value'] = $text;
                return $this->html->tag('input', '', $attributes, true);
            default:
                return $this->html->tag('button', $text, $attributes);
        }
    }

    public function select($name, $options = array()) {
        $options += array(
            'name' => $name,
            'options' => array(),
            'value' => null,
            'empty' => false
        );

        $select_options = array_unset($options, 'options');
        $select_value = array_unset($options, 'value');

        if(($empty = array_unset($options, 'empty')) !== false) {
            $keys = array_keys($select_options);
            if(is_array($empty)) {
                $empty_keys = array_keys($empty);
                $key = $empty_keys[0];
                $values = array_merge($empty, $select_options);
            }
            else {
                $key = $empty;
                $values = array_merge(array($empty), $select_options);
            }
            array_unshift($keys, $key);
            $select_options = array_combine($keys, $values);
        }

        $content = '';
        foreach($select_options as $key => $value) {
            $option = array('value' => $key);
            if((string) $key === (string) $select_value) {
                $option['selected'] = true;
            }
            $content .= $this->html->tag('option', $value, $option);
        }

        return $this->html->tag('select', $content, $options);
    }

    public function radio($name, $options = array()) {
        $options += array(
            'options' => array(),
            'value' => null,
            'legend' => Inflector::camelize($name)
        );
        $radio_options = array_unset($options, 'options');
        $radio_value = array_unset($options, 'value');
        if($legend = array_unset($options, 'legend')) {
            $content = $this->html->tag('legend', $legend);
        }

        $content = '';
        foreach($radio_options as $key => $value) {
            $radio_attr = array(
                'type' => 'radio',
                'value' => $key,
                'id' => Inflector::camelize($name . '_' . $key),
                'name' => $name
            );
            if((string) $key === (string) $radio_value) {
                $radio_attr['checked'] = true;
            }
            $for = array('for' => $radio_attr['id']);
            $content .= $this->html->tag('input', '', $radio_attr, true);
            $content .= $this->html->tag('label', $value, $for);
        }

        return $this->html->tag('fieldset', $content);
    }

    public function input($name, $options = array()) {
        $options += array(
            'name' => $name,
            'type' => 'text',
            'id' => 'Form' . Inflector::camelize($name),
            'label' => Inflector::humanize($name),
            'div' => true,
            'value' => ''
        );

        $label = array_unset($options, 'label');
        $div = array_unset($options, 'div');
        $type = $options['type'];

        switch($options['type']) {
            case 'select':
                unset($options['type']);
                $input = $this->select($name, $options);
                break;
            case 'radio':
                $options['legend'] = $label;
                $label = false;
                $input = $this->radio($name, $options);
                break;
            case 'textarea':
                unset($options['type']);
                $value = Sanitize::html(array_unset($options, 'value'));
                $input = $this->html->tag('textarea', $value, $options);
                break;
            case 'hidden':
                $div = $label = false;
            default:
                if($name == 'password') {
                    $options['type'] = 'password';
                }
                $options['value'] = Sanitize::html($options['value']);
                $input = $this->html->tag('input', '', $options, true);
        }

        if($label) {
            $for = array('for' => $options['id']);
            $input = $this->html->tag('label', $label, $for) . $input;
        }

        if($div) {
            $input = $this->div($div, $input, $type);
        }

        return $input;
    }

    protected function div($class, $content, $type) {
        $attr = array(
            'class' => 'input ' . $type
        );

        if(is_array($class)) {
            $attr = $class + $attr;
        }
        elseif(is_string($class)) {
            $attr['class'] .= ' ' . $class;
        }

        return $this->html->tag('div', $content, $attr);
    }
}