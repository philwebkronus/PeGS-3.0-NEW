<?php

/**
 * @Author: aqdepliyan
 * @DateCreated: 2014-10-28 2:25PM
 *@Description: A class for creating logs adapted from MI_Logger.php of Kronus Cashier
 */

class Logger {

    public $api_logname;
    public $errorType = array(
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSING ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT NOTICE',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        'Exception' => 'Exception',
        'INFO' => 'INFO',
        'ALERT' => 'ALERT',
        'DEBUG' => 'DEBUG',
        'DEBUG' => 'DEBUG',
        'Request' => 'REQUEST',
        'Response' => 'RESPONSE',
    );
    public $logsdir;


    public function __construct($logsdir) {
        $this->logsdir =$logsdir ;
    }

    function log($message, $type, $newLine = false) {

        $logname = $this->uDateTime('Y_m_d') . '.log';
        
        if (!isset($this->errorType))
            $this->errorType = 'Unknown';

        $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . '][' . $type. ']' . $message . "\n";

        if ($newLine)
            $message = "\n" . $message;

        // do not print INFO if DEBUG is false
        if ($this->errorType == 'INFO')
            return true;

        if (!file_exists($this->logsdir . $logname)) {
            $create_file = fopen($this->logsdir . $logname, "w+"); //create the new file
            chmod($this->logsdir . $logname . $logname, 0777); //set the appropriate permissions.
            fclose($create_file);
        }
        if (file_put_contents($this->logsdir . $logname, $message, FILE_APPEND) === false)
            throw new Exception($this->logsdir . $logname . ' is not writable');
    }
    
    function apirequestlog($message, $type, $newLine = false) {
        $logname = "Pcws_" . $this->uDateTime('Y_m_d') . '.log';
        $this->api_logname = $logname;
        
        if (!isset($this->errorType))
            $this->errorType = 'Unknown';

        $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . '][' . $this->errorType[$type]  . ']'.$message."\n";

        if ($newLine)
            $message = "\n" . $message;

        if ($this->errorType == 'INFO')
            return true;

        if (!file_exists($this->logsdir . $logname)) {
            $create_file = fopen($this->logsdir . $logname, "w+"); //create the new file
            chmod($this->logsdir . $logname, 0777); //set the appropriate permissions.
            fclose($create_file);
        }
        if (file_put_contents($this->logsdir . $this->api_logname, $message, FILE_APPEND) === false)
            throw new Exception($this->logsdir . $this->api_logname . ' is not writable');
    }

    function sysLog($message, $type) {
        $trace = debug_backtrace();
        $last_trace = $trace[1];
        if (!isset($last_trace['file']))
            $last_trace['file'] = 'unknown';
        if (!isset($last_trace['line']))
            $last_trace['line'] = 0;

        $message = "\"$message\"" . ' File: ' . $last_trace['file'] . ' LINE: ' . $last_trace['line'];

        $this->log($message, $type);
    }

    function uDateTime($format, $utimestamp = null) {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

    function logger($message, $type = E_ERROR,$isapi = false) {
        $trace = debug_backtrace();

        $last_trace = $trace[0];
        if (!isset($last_trace['file']))
            $last_trace['file'] = 'unknown';
        if (!isset($last_trace['line']))
            $last_trace['line'] = 0;

        if(!$isapi){
            $this->log($message, $type);
        } else {
            $this->apirequestlog($message, $type);
        }     
    }

}

?>