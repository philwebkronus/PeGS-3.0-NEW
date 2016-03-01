<?php

/**
 * Date Created 11 4, 11 11:59:31 AM <pre />
 * Description of MI_HTML
 * @author Bryan Salazar
 */
class MI_HTML {
    
    /**
     * Description: Create a label tag
     * @param object $model
     * @param string $attribute (attrbiute of model)
     * @param string $caption (display in label)
     * @param array $properties (properties of label) example: array('width'=>'100px')
     * @return string (label tag)
     */
    public static function label($model,$attribute,$caption,$properties=array()) {
        $attr = self::getNameID($model, $attribute,false,true);
        $label_attributes = self::getInputAttributes($attr,$properties);
        $label = '<label ' . $label_attributes . '>';
        $label.= $caption .'</label>';
        return $label;
    }
    
    /**
     * Description: Create input text tag
     * @param object $model
     * @param string $attribute (attribute of model)
     * @param array $properties (properties of input tag) example: array('width'=>'100px')
     * @return string (input type="text" tag)
     */
    public static function inputText($model,$attribute,$properties=array()) {
//        $attr = self::getNameValueID($model, $attribute);
//        $input_attributes = self::getInputAttributes($attr,$properties);
//        return '<input type="text" ' . $input_attributes . ' />';
        return self::inputTag('text', $model, $attribute,$properties);  
    }
    
    /**
     * Description: Create input hidden tag
     * @param object $model
     * @param string $attribute (attribute of model)
     * @param array $properties (properties of input tag) example: array('width'=>'100px')
     * @return string (input type="hidden" tag)
     */
    public static function inputHidden($model,$attribute,$properties=array()) {
        return self::inputTag('hidden', $model, $attribute,$properties);  
    }
   
    /**
     * Description: Create input password tag
     * @param object $model
     * @param string $attribute
     * @param array $properties
     * @return string (input type="hidden" tag)
     */
    public static function inputPassword($model,$attribute,$properties=array()) {
//        $attr = self::getNameValueID($model, $attribute);
//        $input_attributes = self::getInputAttributes($attr,$properties);
//        return '<input type="password" ' . $input_attributes . ' />';
        return self::inputTag('password', $model, $attribute,$properties);  
    }
    
    /**
     * Description: Helper function for creating input tag
     * @param string $type
     * @param object $model
     * @param string $attribute
     * @param array $properties
     * @return string input tag 
     */
    private static function inputTag($type,$model,$attribute,$properties=array()) {
        $attr = self::getNameValueID($model, $attribute);
        $input_attributes = self::getInputAttributes($attr,$properties);
        return '<input type="'.$type.'" ' . $input_attributes . ' />';
    }

    /**
     * Description: Create select tag
     * @param object $model
     * @param string $attribute
     * @param array $list example: array('1'=>'foo','2'=>'bar')
     * @param array $default (first option tag)
     * @param array $last (last option tag)
     * @param array $properties
     * @return string (select tag)
     */
    public static function dropDown($model,$attribute,$list,$default=array(),$last=array(),$properties=array()) {
        if($default)
            $list = array_merge($default,$list);
        if($last)
            $list = array_merge ($list,$last);
        
        $attr = self::getNameID($model, $attribute);
        $html_attributes = self::getInputAttributes($attr,$properties);
        $select = '<select ' . $html_attributes . ' >';
        
        foreach($list as $value => $label) {
            if($model->$attribute === $value) {
                $select .= '<option selected="selected" value="'.$value.'" >'.$label . '</option>';
            } else {
                $select .= '<option value="'.$value.'">'.$label.'</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }
    
    /**
     * Description: Create select tag where list is two dimensional array
     * @param object $model
     * @param string $attribute
     * @param array $list (two dimensional array) example: array(array('mykey'=>'foo','myvalue'=>'bar'),array('mykey'=>'test','myvalue'=>'yes'))
     * @param string $opt_val example: referred to param $list example `mykey`
     * @param type $opt_label example: referred to param $list example `myvalue`
     * @param type $default
     * @param type $last
     * @param array $properties
     * @return string (select tag)
     */
    public static function dropDownArray($model,$attribute,$list,$opt_val,$opt_label,$default=array(),$last=array(),$properties=array()) {
//        if($default)
//            $list = array_merge($default,$list);
//        if($last)
//            $list = array_merge ($list,$last);        
        
        $attr = self::getNameID($model, $attribute);
        $html_attributes = self::getInputAttributes($attr,$properties);
        $select = '<select ' . $html_attributes . ' >';
        if($default) {
            foreach($default as $value => $label) {
                if($model->$attribute === $value) {
                    $select .= '<option selected="selected" value="'.$value.'" >'.$label . '</option>';
                } else {
                    $select .= '<option value="'.$value.'">'.$label.'</option>';
                }
            }
        }
        foreach($list as $value) {
            if($model->$attribute === $value[$opt_val]) {
                $select .= '<option selected="selected" value="'.$value[$opt_val].'" >' . $value[$opt_label] . '</option>';
            } else {
                $select .= '<option value="'.$value[$opt_val].'">'.$value[$opt_label].'</option>';
            }
        }
        $select .= '</select>';
        return $select;
    }
    
    /**
     * Description: helper function
     * @param MI_Model $model
     * @param string $attribute
     * @return array 
     */
    private static function getNameValueID($model,$attribute) {
        if(!$model instanceof MI_Model) {
            throw new Exception(get_class($model) . ' should have parent MI_Model');
        }
        $class_name = get_class($model);
        $name = $class_name . '[' . $attribute . ']';
        $id = $class_name . '_' . $attribute;
        $attr = array(
            'name'  => $name,
            'value' => $model->$attribute,
            'id'    => $id,
        );
        return $attr;
    }
    
    /**
     * Description: helper function
     * @param MI_Model $model
     * @param string $attribute
     * @param boolean $has_id
     * @param boolean $isLabel
     * @return array 
     */
    private static function getNameID($model, $attribute,$has_id=true,$isLabel=false) {
        if(!$model instanceof MI_Model) {
            throw new Exception(get_class($model) . ' should have parent MI_Model');
        }
        $class_name = get_class($model);
        $name = $class_name . '[' . $attribute . ']';
        $id = $class_name . '_' . $attribute;
        $attr = array(
            'name'  => $name
        );
        if($has_id)
            $attr = array_merge($attr,array('id'=>$id));
        
        if($isLabel)
            $attr = array_merge($attr,array('for'=>$id));
        return $attr;
    }
    
    /**
     * Description: helper function
     * @param array $attr
     * @param array $properties
     * @return string 
     */
    private static function getInputAttributes($attr,$properties) {
        $prop = array_merge($attr,$properties);
        $input_attributes = '';
        foreach($prop as $key => $val) {
            $input_attributes.= ' ' . $key . '="' . $val . '"';
        }
        return $input_attributes;
    }
}

