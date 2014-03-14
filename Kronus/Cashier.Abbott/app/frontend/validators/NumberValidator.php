<?php

/**
 * Date Created 11 4, 11 4:32:51 PM <pre />
 * Description of NumberValidator
 * @author Bryan Salazar
 */
class NumberValidator extends MI_Validator{
    public function rules($model, $attribute, $options = array()) {
        $value = $model->$attribute;
        
        if(!is_numeric($value)) {
            throw new Exception($attribute . ' should be numeric');
        }
        
        if(isset($options['max']) && $value > $options['max']) {
            throw new Exception($attribute . ' should be less than or equal to ' . number_format($options['max'],2));
        }
        
        if(isset($options['min']) && $value < $options['min']) {
            throw new Exception($attribute . ' should be greater than or equal to ' . number_format($options['min'],2));
        }
        
        if(isset($options['divisible']) && $value % $options['divisible'] != 0) {
            throw new Exception($attribute . ' should be divisible by ' . number_format($options['divisible'],2));
        }
        
    }
}

