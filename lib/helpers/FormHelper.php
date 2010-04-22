<?php

class FormHelper extends Helper {
    public function create($action = null, $options = array()) {
        $options += array(
            'method' => 'post',
            'action' => Mapper::url($action)
        );
        
        if($options['method'] == 'file'):
            $options['method'] = 'post';
            $options['enctype'] = 'multipart/form-data';
        endif;
        
        return $this->html->openTag('form', $options);
    }
    public function close($submit = null, $attributes = array()) {
        $form = $this->closeTag('form');

        if(!is_null($submit)):
            $form = $this->submit($submit, $attributes) . $form;
        endif;

        return $form;
    }
    public function submit($text, $attributes = array()) {
        $attributes += array(
            'type' => 'submit',
            'tag' => 'button'
        );
        switch(array_unset($attributes, 'tag')):
            case 'image':
                $attributes['alt'] = $text;
                $attributes['type'] = 'image';
                $attributes['src'] = $this->assets->image($attributes['src']);
            case 'input':
                $attributes['value'] = $text;
                return $this->html->tag('input', '', $attributes, true);
            default:
                return $this->html->tag('button', $text, $attributes);
        endswitch;
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
        
        if(($empty = array_unset($options, 'empty')) !== false):
            $keys = array_keys($select_options);
            if(is_array($empty)):
                $empty_keys = array_keys($empty);
                $key = $empty_keys[0];
                $values = array_merge($empty, $select_options);
            else:
                $key = $empty;
                $values = array_merge(array($empty), $select_options);
            endif;
            array_unshift($keys, $key);
            $select_options = array_combine($keys, $values);
        endif;
        
        $content = '';
        foreach($select_options as $key => $value):
            $option = array('value' => $key);
            if((string) $key === (string) $select_value):
                $option['selected'] = true;
            endif;
            $content .= $this->html->tag('option', $value, $option);
        endforeach;
        
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
        if($legend = array_unset($options, 'legend')):
            $content = $this->html->tag('legend', $legend);
        endif;
        
        $content = '';
        foreach($radio_options as $key => $value):
            $radio_attr = array(
                'type' => 'radio',
                'value' => $key,
                'id' => Inflector::camelize($name . '_' . $key),
                'name' => $name
            );
            if((string) $key === (string) $radio_value):
                $radio_attr['checked'] = true;
            endif;
            $for = array('for' => $radio_attr['id']);
            $content .= $this->html->tag('input', null, $radio_attr, true);
            $content .= $this->html->tag('label', $value, $for);
        endforeach;
        
        return $this->html->tag('fieldset', $content);
    }
    public function date($name, $options = array()) {
        if(is_array($options['value'])):
            $date = mktime(0, 0, 0, $v['m'], $v['d'], $v['y']);
        elseif(!is_null($options['value'])):
            $date = strtotime($options['value']);
        else:
            $date = time();
        endif;

        $options += array(
            'value' => null,
            'startYear' => 1980,
            'endYear' => date('Y'),
            'currentDay' => date('j', $date),
            'currentMonth' => date('n', $date),
            'currentYear' => date('Y', $date),
            'format' => 'dmy'
        );

        $days = array_range(1, 31);
        $months = array_range(1, 12);
        $years = array_range($options['startYear'], $options['endYear']);
        
        $select_day = $this->select($name . '[d]', array(
            'value' => $options['currentDay'],
            'options' => $days,
            'id' => $options['id'] . 'D'
        ));
        
        $select_month = $this->select($name . '[m]', array(
            'value' => $options['currentMonth'],
            'options' => $months,
            'id' => $options['id'] . 'M'
        ));
        
        $select_year = $this->select($name . '[y]', array(
            'value' => $options['currentYear'],
            'options' => $years,
            'id' => $options['id'] . 'Y'
        ));
        
        if($format == 'ymd'):
            return $select_year . $select_month . $select_day;
        else:
            return $select_day . $select_month . $select_year;
        endif;
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

        switch($options['type']):
            case 'select':
                unset($options['type']);
                $input = $this->select($name, $options);
                break;
            case 'radio':
                $options['legend'] = $label;
                $label = false;
                $input = $this->radio($name, $options);
                break;
            case 'date':
                $input = $this->date($name, $options);
                $options['id'] = $options['id'] . 'D';
                break;
            case 'textarea':
                unset($options['type']);
                $value = Sanitize::html(array_unset($options, 'value'));
                $input = $this->html->tag('textarea', $value, $options);
                break;
            case 'hidden':
                $div = $label = false;
            default:
                if($name == 'password'):
                    $options['type'] = 'password';
                endif;
                $options['value'] = Sanitize::html($options['value']);
                $input = $this->html->tag('input', null, $options, true);
        endswitch;

        if($label):
            $for = array('for' => $options['id']);
            $input = $this->html->tag('label', $label, $for) . $input;
        endif;

        if($div):
            if($div === true):
                $div = 'input ' . $type;
            elseif(is_array($div)):
                $div += array('class' => 'input ' . $options['type']);
            endif;
            $input = $this->div($input, $div);
        endif;

        return $input;
    }
    public function div($content, $attr = array()) {
        if(!is_array($attr)):
            $attr = array('class' => $attr);
        endif;
        
        return $this->html->tag('div', $content, $attr);
    }
}