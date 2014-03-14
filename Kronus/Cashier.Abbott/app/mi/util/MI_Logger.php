<?php

/**
 * Date Created 11 22, 11 9:07:08 AM <pre />
 * Description of MI_Logger
 * @author Bryan Salazar
 */
class MI_Logger {
    public static $file_name = 'application.log';
    public static $casino_logname;
    
    static private $_errorType = array(
        E_ERROR              => 'ERROR',
        E_WARNING            => 'WARNING',
        E_PARSE              => 'PARSING ERROR',
        E_NOTICE             => 'NOTICE',
        E_CORE_ERROR         => 'CORE ERROR',
        E_CORE_WARNING       => 'CORE WARNING',
        E_COMPILE_ERROR      => 'COMPILE ERROR',
        E_COMPILE_WARNING    => 'COMPILE WARNING',
        E_USER_ERROR         => 'USER ERROR',
        E_USER_WARNING       => 'USER WARNING',
        E_USER_NOTICE        => 'USER NOTICE',
        E_STRICT             => 'STRICT NOTICE',
        E_RECOVERABLE_ERROR  => 'RECOVERABLE ERROR',
        'Exception'          => 'Exception',
        'INFO'               => 'INFO',
        'ALERT'              => 'ALERT',
        'DEBUG'              => 'DEBUG',
        'DEBUG'              => 'DEBUG',
        'Request'           => 'REQUEST',
        'Response'           => 'RESPONSE',
    );
    
    static private $_casino = array(
        'MG'            =>  'MG',
        'RTG'            =>  'RTG',
        'PT'            =>  'PT',
    );
    
    
    /**
     * Sample config:
     *  logs => array(
     *      // path for the log file
     *      'log_path'=>Mirage::app()->getAppPath() . DIRECTORY_SEPARATOR . 'logs',
     * 
     *      // will rotate only in MIRAGE_DEBUG is false
     *      'rotate'=>true,
     * 
     *      // day or filesize
     *      'rotate_by'=>'day',
     * 
     *      // will rotate per day
     *      'interval'=>1,
     * 
     *      // will rotate per MB
     *      //'filesize'=>5,    
     *  )
     * 
     * @param type $logs 
     */
    
    
    public static function log($message,$type,$newLine = false, $casino = '') {
        self::_rotate();
        $file_name = self::_getFileName();
 
        if(!isset(self::$_casino[$casino]))
            self::$_casino[$casino] = '';
        
        if(!isset(self::$_errorType[$type]))
            self::$_errorType[$type] = 'Unknown';

        switch (self::$_casino[$casino]){
            case "RTG":
                $message = '[' . self::uDateTime('Y-m-d H:i:s.u') . ']['.self::$_casino[$casino].'][' . self::$_errorType[$type] . '] ' . $message . "\n";
                break;
            case "MG":
                $message = '[' . self::uDateTime('Y-m-d H:i:s.u') . ']['.self::$_casino[$casino].'][' . self::$_errorType[$type] . '] ' . $message . "\n";
                break;
            case "PT":
                $message = '[' . self::uDateTime('Y-m-d H:i:s.u'). ']['.self::$_casino[$casino].'][' . self::$_errorType[$type] . '] ' . $message . "\n";
                break;
            default:
                $message = '[' . self::uDateTime('Y-m-d H:i:s.u') . ']' . '[' . self::$_errorType[$type] . '] ' . $message . "\n";
                break;
        }
        
        if($newLine)
            $message = "\n" . $message;

        // do not print INFO if MIRAGE_DEBUG is false
        if(self::$_errorType[$type] == 'INFO' && !MIRAGE_DEBUG)
            return true;

        $config = Mirage::app()->getConfig();
        $logs = $config['logs'];
        $log_path = $logs['log_path'] . DIRECTORY_SEPARATOR;
        
        if(file_put_contents($log_path . $file_name, $message, FILE_APPEND) === false)
            throw new Exception($log_path . self::$file_name . ' is not writable');     
    }
    
    public static function casinolog($message,$type,$newLine = false, $casino = '') {
        $logname = "Casino_".self::uDateTime('Y_m_d').'.log';
        self::$casino_logname = $logname;
 
        if(!isset(self::$_casino[$casino]))
            self::$_casino[$casino] = '';
        
        if(!isset(self::$_errorType[$type]))
            self::$_errorType[$type] = 'Unknown';

        switch (self::$_casino[$casino]){
            case "RTG":
                $message = '[' . self::uDateTime('Y-m-d H:i:s.u') . '][' . self::$_errorType[$type] . ']['.self::$_casino[$casino].'] ' . $message . "\n";
                break;
            case "MG":
                $message = '[' . self::uDateTime('Y-m-d H:i:s.u') . '][' . self::$_errorType[$type] . ']['.self::$_casino[$casino].'] ' . $message . "\n";
                break;
            case "PT":
                $message = '[' . self::uDateTime('Y-m-d H:i:s.u'). '][' . self::$_errorType[$type] . ']['.self::$_casino[$casino].'] ' . $message . "\n";
                break;
        }
        
        if($newLine)
            $message = "\n" . $message;

        // do not print INFO if MIRAGE_DEBUG is false
        if(self::$_errorType[$type] == 'INFO' && !MIRAGE_DEBUG)
            return true;

        $config = Mirage::app()->getConfig();
        $logs = $config['logs'];
        $log_path = $logs['log_path'] . DIRECTORY_SEPARATOR;  
        
//        if(file_exists($log_path. self::$file_name)){
            if(file_put_contents($log_path .  self::$casino_logname, $message, FILE_APPEND) === false)
                    throw new Exception($log_path .self::$casino_logname . ' is not writable');   
//        } else {
//            $handle = fopen($log_path. self::$file_name, 'w') or die('Cannot open file:  '.$log_path. self::$file_name);
//            fwrite($handle, $message);
//        }   
    }
    
    public static function sysLog($message,$type) {
        $trace = debug_backtrace();
        $last_trace = $trace[1];
        if(!isset($last_trace['file']))
            $last_trace['file'] = 'unknown';
        if(!isset($last_trace['line']))
            $last_trace['line'] = 0;

        $message = "\"$message\"" . ' File: ' . $last_trace['file'] . ' LINE: ' . $last_trace['line'];
        
        self::log($message, $type);
    }
    
    /**
     * Description: Helper function to rotate the log file
     * @return type 
     */
    private static function _rotate() {
        $config = Mirage::app()->getConfig();
        if(!isset($config['logs']))
            throw new Exception('Please set key "logs" in config');
        
        if(!is_array($config['logs']))
            throw new Exception('"logs" should be array');
        
        $logs = $config['logs'];
        
        if(!isset($logs['log_path']))
            throw new Exception('Please set log_path in config');
        
        if(!file_exists($logs['log_path']))
            throw new Exception ('Directory not exist' . $logs['log_path']);
        
        if(!is_dir($logs['log_path']))
            throw new Exception($logs['log_path'] . ' should be directory');
        
        $log_file = $logs['log_path'] . DIRECTORY_SEPARATOR . self::$file_name;
        $log_path = $logs['log_path'] . DIRECTORY_SEPARATOR;
        
//        if(!chmod($logs['log_path'], 775))
//            throw new Exception('Can\'t change permission of ' . $logs['log_path']);        
        
        if(MIRAGE_DEBUG) {
            return true;
        }  
        
        // dont rotate if log file not exist
        if(!file_exists($log_file))
            return true;
        
        if(!isset($logs['rotate_by']))
            throw new Exception('Please set rotate_by');
        
        if($logs['rotate_by'] == 'filesize') {
            if(!isset($logs['filesize']))
                throw new Exception('Please configure log filesize');

            $new_name = self::$file_name;

            if((filesize($log_file) / 1048576) > $logs['filesize'])
                    $new_name = date("Y_m_d",filemtime($log_path . self::$file_name)) .'.log';
            
            // if new name exist dont rotate
            if(file_exists($log_path . $new_name))
               return true;
            
            // rename log file
            if(!rename($log_path . self::$file_name, $log_path . $new_name))
                throw new Exception($log_path . self::$file_name . ' is not writable');
        } else {
            if(!isset($logs['interval']))
                throw new Exception ('Please configure log interval');
            
                    $filetime = filemtime($log_path . self::$file_name);
                    if($filetime != false) {
                        $new_name = date("Y_m_d",$filetime) . '.log';
                    } else {
                        $new_name = self::$file_name;
                    }
                    
                    // do not rotate if file is already exist
                    if(file_exists($log_path . $new_name))
                        return true;

                    $content = file($log_file);

                    // do not rotate if file is empty
                    if(!isset($content[0]))
                        return true;
                    
                    $date = substr($content[0], 1, 10);
                    $d = explode('-',$date);
                    $new_name = $d[0] . '_' . $d[1] . '_' . $d[2] . '.log';
                if(file_exists($log_path . $new_name)) {
                    unlink($log_path . $new_name);
                }
            $interval = $logs['interval'];
            
            $max_date = strtotime("+$interval day",strtotime($date));
            $date_today = strtotime(date('Y-m-d'));
            if($max_date <= $date_today) {
                // rename log file
                if(!rename($log_path . self::$file_name, $log_path . $new_name))
                    throw new Exception($log_path . self::$file_name . ' is not writable');                
            }
            
        }
    }
    
    /**
     *
     * @param string $file_name 
     */
    public static function setLogFileName($file_name) {
        self::$file_name = $file_name;
    }
    
    /**
     * Desscription:
     * @return string 
     */
    static private function _getFileName() {
        if(MIRAGE_DEBUG)
            return $filename = 'dev_'. self::$file_name;
        return self::$file_name;
    }    
    
    static public function uDateTime($format, $utimestamp = null) {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format),$timestamp);
    }    
}

