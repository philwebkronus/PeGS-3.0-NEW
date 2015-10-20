<?php

/**
 * Date Created 11 4, 11 1:04:53 PM <pre />
 * Description of StringValidation
 * @author Bryan Salazar
 */
class StringValidator extends MI_Validator {
    public function rules($model, $attribute,$options = array(),$msg='') {
        $value = trim($model->$attribute);
        
        $message = $attribute . ' cannot be empty';
        if($value == null) {
            if($msg)
                throw new Exception($msg);
            throw new Exception($message);
        } else if($value == '') {
            if($msg)
                throw new Exception($msg);
            throw new Exception($message);
        }
        if(isset($options['min']) && strlen($value) < $options['min']) {
            if($msg)
                throw new Exception($msg);
            throw new Exception($attribute . ' length should be greater than or equal to ' . $options['min']);
        }
        
        if(isset($options['compare'])) {
            $opt = $options['compare'];
            $attr = $opt['attr'];
            $val2 = $model->$attr;
            $comparator = $opt['comparator'];
            if(eval("return \$value $comparator \$val2;")) {
                
            } else {
                if($msg)
                    throw new Exception($msg);
                throw new Exception($message);
            }
        }
    }
}

