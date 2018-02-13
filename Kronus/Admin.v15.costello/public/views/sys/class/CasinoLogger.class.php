<?php

/*
 * Created by : Junjun S. Hernandez
 * Date Created : May 28, 2013
 * Description: A class for creating Casino Logs adapted from MI_Logger.php of cashier2
 */

class CasinoLogger {

    public $casino_logname;
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

    function log($message, $type, $newLine = false, $casino = '') {

        if (!isset($this->errorType))
            $this->errorType = 'Unknown';

        switch ($casino) {
            case "RTG":
                $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . '][' . $casino . '][' . $this->errorType[$type] . '] ' . $message . "\n";
                break;
            // Comment Out CCT 02/06/2018 BEGIN
            //case "MG":
            //    $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . '][' . $casino . '][' . $this->errorType[$type] . '] ' . $message . "\n";
            //    break;
            //case "PT":
            //    $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . '][' . $casino . '][' . $this->errorType[$type] . '] ' . $message . "\n";
            //    break;
            // Comment Out CCT 02/06/2018 END
            default:
                $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . ']' . '[' . $casino . '] ' . $message . "\n";
                break;
        }

        if ($newLine)
            $message = "\n" . $message;

        // do not print INFO if DEBUG is false
        if ($this->errorType == 'INFO')
            return true;

        $log_path = ROOT_DIR . 'sys/log/' . DIRECTORY_SEPARATOR;
        if (!file_exists($log_path . $logname)) {
            $create_file = fopen($log_path . $logname, "w+"); //create the new file
            chmod($file, 0777); //set the appropriate permissions.
            fclose($create_file);
        }
        if (file_put_contents($log_path . $file_name, $message, FILE_APPEND) === false)
            throw new Exception($log_path . $this->$file_name . ' is not writable');
    }

    function casinolog($message, $type, $newLine = false, $casino = '') {
        $logname = "Casino_" . $this->uDateTime('Y_m_d') . '.log';
        $this->casino_logname = $logname;

        if (!isset($this->errorType))
            $this->errorType = 'Unknown';

        switch ($casino) {
            case "RTG":
                $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . '][' . $this->errorType[$type] . '][' . $casino . '] ' . $message . "\n";
                break;
            // Comment Out CCT 02/06/2018 BEGIN            
            //case "MG":
            //    $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . '][' . $this->errorType[$type] . '][' . $casino . '] ' . $message . "\n";
            //    break;
            //case "PT":
            //    $message = '[' . $this->uDateTime('Y-m-d H:i:s.u') . '][' . $this->errorType[$type] . '][' . $casino . '] ' . $message . "\n";
            //    break;
            // Comment Out CCT 02/06/2018 END
        }

        if ($newLine)
            $message = "\n" . $message;

        if ($this->errorType == 'INFO')
            return true;

        $log_path = ROOT_DIR . 'sys/log/' . DIRECTORY_SEPARATOR;
        if (!file_exists($log_path . $logname)) {
            $create_file = fopen($log_path . $logname, "w+"); //create the new file
            chmod($log_path . $logname, 0777); //set the appropriate permissions.
            fclose($create_file);
        }
        if (file_put_contents($log_path . $this->casino_logname, $message, FILE_APPEND) === false)
            throw new Exception($log_path . $this->casino_logname . ' is not writable');
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

        $log_path = ROOT_DIR . 'sys/log/' . DIRECTORY_SEPARATOR;
        if (!file_exists($log_path . $logname)) {
            $create_file = fopen($log_path . $logname, "w+"); //create the new file
            chmod($log_path . $logname, 0777); //set the appropriate permissions.
            fclose($create_file);
        }
        if (file_put_contents($log_path . $this->api_logname, $message, FILE_APPEND) === false)
            throw new Exception($log_path . $this->api_logname . ' is not writable');
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

    function logger($message, $type = E_ERROR, $casino = '',$isapi = false) {
        $trace = debug_backtrace();

        $last_trace = $trace[0];
        if (!isset($last_trace['file']))
            $last_trace['file'] = 'unknown';
        if (!isset($last_trace['line']))
            $last_trace['line'] = 0;

        if ($casino == '') {
            if(!$isapi){
                if (isset($_SESSION['site_code'])) {
                    $message = "\"$message\"" . ' SiteID = ' . $_SESSION['AccountSiteID'] . ' File: ' . $last_trace['file'] . ' LINE: ' . $last_trace['line'];
                } else {
                    $message = "\"$message\"" . ' File: ' . $last_trace['file'] . ' LINE: ' . $last_trace['line'];
                }
                $this->log($message, $type);
            } else {
                $this->apirequestlog($message, $type);
            }
            
        } else {
            $message = $message;
            $this->casinolog($message, $type, false, $casino);
        }
    }

}

?>