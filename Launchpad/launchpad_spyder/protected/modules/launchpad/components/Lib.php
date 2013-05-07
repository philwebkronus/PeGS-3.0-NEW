<?php
/**
 * General Purpose Library
 * @author: Bryan Salazar
 * @package application.modules.launchpad.components
 */

class Lib {
	
    /**
     * Shorthand for CVarDumper::dump();
     * @param mixed $var variable you want to dump
     * @param int $depth number of depth to be display
     * @param bool $highlight true if you want colored
     */	
    public static function debug($var,$depth=100,$highlight=true)
    {
        CVarDumper::dump($var, $depth, $highlight);	
    }
    
    /**
    * Shorthand for CVarDumper::dumpAsString();
    * @param mixed $var variable you want to dump
    * @param int $depth number of depth to be display
    * @param bool $highlight true if you want colored
    * @return string 
    */	
    public static function debugAsString($var,$depth=100,$highlight=true)
    {
        return CVarDumper::dumpAsString($var, $depth, $highlight);	
    }
	
    /**
     * Check if browser is IE and PEGS Launchpad browser
     * @return bool 
     */	
    public static function isIEBrowser()
    {
        $agent = (isset($_SERVER['HTTP_USER_AGENT']))?$_SERVER['HTTP_USER_AGENT'] : '';

        //if((stripos($agent, 'msie') !== false) && (stripos($agent, 'opera') === false))
        if((stripos($agent, 'msie') !== false) || (stripos($agent, 'PEGS Launchpad v') !== false))
                        return true;
        return false;	
    }
        
    /**
     *
     * @param string $codeOrType
     */
//    public static function getCasinoDivID($codeOrType) 
//    {
//        switch ($codeOrType) {
//            case 'MM':
//                $id='magic-macau';
//                break;
//            case 'SW':
//                $id='slots-world';
//                break;
//            case 'VV':
//                $id='vibrant-vegas';
//                break;
//        }
//        return $id;
//    }
    
    public static function getCasinoName($codeOrType)
    {
        switch ($codeOrType) {
            case 'MM':
                $casinoName='magic-macau';
                break;
            case 'SW':
                $casinoName='slots-world';
                break;
            case 'VV':
                $casinoName='vibrant-vegas';
                break;
            case 'SS':
                $casinoName='swinging-singapore';
                break;
        }
        return $casinoName;
    }
    
    public static function getImage($codeOrType)
    {
        switch ($codeOrType) {
            case 'MM':
                $image='magic_macau';
                break;
            case 'SW':
                $image='slots_world';
                break;
            case 'VV':
                $image='vibrant_vegas';
                break;
            case 'SS':
                $image='rockin_reno';
                break;
        }
        return $image;
    }
    
    public static function getDisableImage($codeOrType)
    {
        switch ($codeOrType) {
            case 'MM':
                $image='magic_macau_deactivated.jpg';
                break;
            case 'SW':
                $image='slots_world_deactivated.jpg'; // temp
                break;
            case 'VV':
                $image='vibrant_vegas_deactivated.jpg';
                break;
        }
        return $image;
    }
    
    public static function removeVip($terminalCode)
    {
        // case-insensitive
        $terminalCode = preg_replace('/vip/i', '', $terminalCode);
        return $terminalCode;
    }
    
    public static function moneyToDecimal($moneyFormatted)
    {
        return str_replace(',', '', $moneyFormatted);
    }
    
    public static function udate($format, $utimestamp = null)
    {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }    
}
