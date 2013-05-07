<?php

/**
 * @author owliber
 * @date Oct 2, 2012
 * @filename CodeGenerator.php
 * 
 */
class CodeGenerator extends Controller
{

    /**
     * 
     * @param int $length
     * @param int $num
     * @return type
     */        
    public function generateCode($length, $num)
    {
        $numeric_set = '0123456789';
        
        $codes = array();
        $num_length = strlen($numeric_set);
        $num_created = 0;
        
        while ($num_created < $num)
        {
            $code = CodeGenerator::generate_codes($length, $numeric_set, $num_length);
            if (isset($codes[$code]))
                continue;
            $codes[$code] = true;
            $num_created++;
        }
        return array_keys($codes);
    }
    
    public function generate_codes($length, $numeric_set, $num_length)
    {
        $code = '';
        while (strlen($code) < $length)
            $code .= CodeGenerator::get_random_code($numeric_set, $num_length);
        return $code;
    }
    
    public function get_random_code($numeric_set, $num_length)
    {
        return substr($numeric_set, mt_rand(0, $num_length - 1), 1);
    }

}
?>
