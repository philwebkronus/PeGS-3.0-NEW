<?php
/**
 * API Request and Request Logger
 * @author Mark Kenneth Esguerra
 * @date Febraury 9, 2015
 */
class Logger 
{
    private $dir;
    private $prefix = "";
    /**
     * Set prefix for file name.
     * @param type $prefix
     */
    public function setPrefix ($prefix)
    {
        $this->prefix = "";
        if (strlen($prefix) > 0)
        {
            $this->prefix = trim($prefix)."_";
        }
    }
    /**
     * Log Request 
     * @param type $method The name of called API method
     * @param array $parameters Input Parameters
     * @param type $details Additional details 
     */
    public function _logRequest($method, $parameters, $details = null)
    {
        $date = $this->uDateTime("Y-m-d H:i:s");
        $log = "[$date][REQUEST][Method] =>".$method." [Input] =>". $parameters;
        
        $this->write($log);
    }
    /**
     * Logs the response of the called API Method
     * @param type $method The name of called API method 
     * @param array $parameters Out Parameters  
     * @param string $returnmsg Return message
     * @param array $details Additional details
     */
    public function _logResponse($method, $parameters, $details = null)
    {
        $date = $this->uDateTime("Y-m-d H:i:s");
        $log = "[$date][RESPONSE][Method] =>".$method." [Output] =>". $parameters;
        
        $this->write($log);
    }
    private function write($text)
    {
        $date = date('Y-m-d');
        $this->dir = ROOT_DIR."/include/log/";

        //opens file, creates if file not exist
        $fw = @fopen($this->dir.$this->prefix.$date.".log", "a");
        if ($fw)
        {
            @fwrite($fw, "$text\r\n");
        }
        @fclose($fw);
    }
    private function uDateTime($format, $utimestamp = null) {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }
}
?>
