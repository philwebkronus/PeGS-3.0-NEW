<?php
/**
 * Utilities | helper
 * @date 10/12/12
 * @author elperez
 */
class Utilities {

    public static function log($message)
    {
        Yii::log($message, 'error');
    }

    public static function validateInput($string){
         if (!preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬]/', $string))
            return true;
         else
            return false;
    }

    /**
     * Description: convert int to money format
     * @param int $value
     * @return string
     */
    public static function toMoney($value) {
        if($value !== '')
            return number_format($value,2);
    }

    public static function toDecimal($value) {
        if($value !== ''){
            //$result = number_format($value,2,".","");
            return (float)$value;
        }
    }

    /**
     * Description: convert money format to int
     * @param int $value
     * @return int
     */
    public static function toInt($value) {
        if($value)
            return str_replace(',', '', $value);
    }

    /**
     * @author fdlsison
     * @param type $string
     * @return boolean
     * @date 6-19-2014
     */

    public static function validateEmail($string) {
        if (preg_match("/^[^@ ]+@[^@ ]+\.[^@ \.]+$/", $string))
            return true;
        else
            return false;
    }

    public static function validateAlphaNumeric($string) {
        if (preg_match("/^[A-Za-z0-9]+$/", $string))
            return true;
        else
            return false;
    }

    //@date 6-25-2014
    public static function validateAlpha($string) {
        if (preg_match("/^[A-Za-z]+$/", $string))
            return true;
        else
            return false;
    }

    public static function validateNumeric($string) {
        if (preg_match("/^[0-9]+$/", $string))
            return true;
        else
            return false;
    }

    //@date 6-30-2014
    public static function formatName($string) {
        $arrNames = explode(" ", $string);
        if (count($arrNames) > 1)
        {
            $name = "";
            foreach($arrNames as $names)
            {
               $n = trim(ucfirst(strtolower($names)));
               $name .= $n." ";
            }
            return trim($name);
        }
        else
        {
            $name = trim(ucfirst(strtolower($string)));
            return trim($name);
        }
   }

   public static function generateAlphaNumeric($strlen) {
       $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
       $string = '';
       for($i=0;$i < $strlen; $i++) {
           $string .= $chars[rand(0, strlen($chars) -1)];
       }
       return $string;
   }

   //@date 07-03-2014
   public static function getMod10($stringVal) {
       $oddValue = $stringVal[0] + $stringVal[2];
       $evenValue = $stringVal[1] + $stringVal[3];
       $mod1 = abs($oddValue * $evenValue) % 10;
       $oddValue = $stringVal[4] + $stringVal[6];
       $evenValue = $stringVal[5];
       $mod2 = abs($oddValue * $evenValue) % 10;
       return $mod1 . $mod2;
   }

   public static function mt_rand_str($length) {
       $c = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $s = '';
       $cl = strlen($c)-1;
       for ($cl = strlen($c)-1, $i = 0; $i < $length; $s .= $c[mt_rand(0, $cl)], ++$i);
       return $s;
   }
}

?>
