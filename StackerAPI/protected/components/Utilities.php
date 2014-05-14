<?php
/**
 * Utilities | helper
 * @date 10/12/12
 * @author elperez
 * @modified JunJun S. Hernandez
 * @datemodified 04/25/2014
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
    
    public static function errorLogger($errormsg, $apiname, $otherinfo)
    {
        $errorfile = date('Y_m_d').".log";
        $logpath = "logs/".$errorfile;
        
        $message = "[".date('Y-m-d H:i:s')."] ".strtoupper($apiname)."; Error: ".$errormsg."; ".$otherinfo."\n \n";
        //Create file if not exist
        if (!file_exists($logpath))
        {
            $openfile = fopen($logpath, "w+");
            chmod($logpath, 0777);
            if (!$openfile)
            {
                throw new Exception("Unable to open write");
            }
            fclose($openfile);
        }
        if (file_put_contents($logpath, $message, FILE_APPEND) ===  false)
        {
            throw new Exception("Unable to write");
        }
    }
}

?>
