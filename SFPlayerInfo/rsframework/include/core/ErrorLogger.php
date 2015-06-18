<?php

class ErrorLogger
{

    public $trans_logname;
    public $logdate;

    function log($date, $type, $message, $newLine = false, $trans = '')
    {
        if (App::getParam("applicationtimezone") != '')
        {
            $date->setTimezone(new DateTimeZone(App::getParam("applicationtimezone")));
        }

        $logname = $this->uDateTime('Y_m_d') . '.log';
        $this->trans_logname = $logname; //Filename of the error log file
        $this->logdate = $this->uDateTime(date($date->format("d-M-Y H:i:s")));
        //$this->logdate = $this->uDateTime("[d-M-Y H:i:s] "); //Line identity of the error log

        if ($newLine)
            $message = "\n" . $message; //Determine the new line

        $log_path = ROOT_DIR . 'include/log/' . DIRECTORY_SEPARATOR; //URL of the file where the error log should be stored
        //Check if the file already exists
        if (!file_exists($log_path . $logname))
        {
            $create_file = fopen($log_path . $logname, "w+"); //create the new file
            chmod($log_path . $logname, 0777); //set the appropriate permissions.
            fclose($create_file);
        }
        //Write error log to file
        if (file_put_contents($log_path . $this->trans_logname, $this->logdate . $type . $message . "\n", FILE_APPEND) === false)
            throw new Exception($log_path . $this->trans_logname . ' is not writable');
    }

    function uDateTime($format, $utimestamp = null)
    {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

    function logger($logdate, $type, $message)
    {
        $trace = debug_backtrace();

        $last_trace = $trace[0];
        if (!isset($last_trace['file']))
            $last_trace['file'] = 'unknown';
        if (!isset($last_trace['line']))
            $last_trace['line'] = 0;

        //Error should be logged to file only when message is not empty
        if (!$message == '')
        {
            $message = "\"$message\"" . ' File: ' . $last_trace['file'] . ' LINE: ' . $last_trace['line'];
            $this->log($logdate, $type, $message);
        }
    }

}

?>
