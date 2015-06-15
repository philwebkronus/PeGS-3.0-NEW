<?

class Logging
{
    var $isOpen = -1;
    var $LogFile;
    var $fp = null;
    var $logdir = '/var/log/jobs/';

    function Logging($logfile = '')
    {
        $this->isOpen = -1;
        if ($logfile)
        {
            $this->LogFile = $logfile;
        }
        else
        {
            $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
            $logfile = $script_name . '.txt';
            $today = date('Y-m-d');
            $this->LogFile = $this->logdir . $today . "_" . $logfile;
        }
    }

    function Open()
    {
        if ($this->isOpen == -1)
        {
            $lfile = $this->LogFile;
            $this->fp = fopen($lfile , 'a') or exit("Can't open $lfile!");
        }
        $this->isOpen = 1;
    }

    function Close()
    {
        if ($this->isOpen)
        {
            fclose($this->fp);
            $this->isOpen = -1;
        }
    }

    function WriteAppend($message='', $timestamp = true)
    {
        if ($this->isOpen == -1)
        {
            $this->Open();
        }

        if ($timestamp == true)
        {
            $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
            $time = date('H:i:s');
            fwrite($this->fp, "$time: $message\r\n");
        }
        else
        {
            fwrite($this->fp, "$message");
        }

        $this->Close();

    }
}


?>