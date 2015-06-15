<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 06 21, 10
 * Company: Philweb
 *****************************/
class File extends BaseObject
{
	var $isOpen = -1;
	var $FileName;
	var $fp = null;
	var $logdir = '/var/log/jobs/';
        public $Relative = false;

        /**
         *
         * @param string $filename The path of the file to use
         */
	function __construct($filename = '')
	{
            $this->isOpen = -1;
            if ($filename)
            {
                $this->FileName = $filename;
            }
            else
            {
                $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
                $filename = $script_name . '.txt';
                $today = date('Y-m-d');
                $this->FileName = $this->logdir . $today . "_" . $filename;
            }
	}

	function Open($openattribute = "a")
	{
            if ($this->isOpen == -1)
            {
                $lfile = $this->FileName;
                
                $this->fp = fopen($lfile , $openattribute) or exit("Can't open $lfile!");
                $this->isOpen = 1;
                
            }
            
	}

        function OpenWrite()
        {
            if ($this->isOpen == -1)
            {
                $filename = $this->FileName;
                try
                {
                    $this->fp = fopen($filename , "w");// or exit("Can't open $filename!");
                    $this->isOpen = 1;
                }
                catch (Exception $e)
                {
                    $this->setError($e->getMessage());
                }

            }
        }

	function Close()
	{
            if ($this->isOpen)
            {
                fclose($this->fp);
                $this->isOpen = -1;
            }
	}

	function WriteAppend($message='')
	{
            if ($this->isOpen == -1)
            {
                $this->Open();
            }

            $retval = fwrite($this->fp, "$message");

            $this->Close();

            return $retval;
	}

        function Write($message='')
	{
            if ($this->isOpen == -1)
            {
                $this->OpenWrite();
            }

            if (!$this->HasError)
            {
                if (fwrite($this->fp, "$message") === false)
                {
                    $this->setError("Cannot write to file $this->FileName");
                    $retval = false;
                }
                else
                {
                    $retval = true;
                }

                $this->Close();
                return $retval;
            }
            else
            {
                return false;
            }
	}

        function WriteBinary($message='')
	{
            if ($this->isOpen == -1)
            {
                $this->Open("wb");
            }

            if (!$this->HasError)
            {
                if (fwrite($this->fp, "$message") === false)
                {
                    $this->setError("Cannot write to file $this->FileName");
                    $retval = false;
                }
                else
                {
                    $retval = true;
                }

                $this->Close();
                return $retval;
            }
            else
            {
                return false;
            }
	}

        function ReadToEnd()
        {
            if ($this->isOpen == -1)
            {
                //$this->Open("r");
            }
            $retval = file_get_contents($this->FileName);
            //$retval = fread($this->fp, filesize($this->FileName));
            
            return $retval;
        }
        
        function GetCurrentDirectory()
        {
            return dirname(__FILE__);
        }
}


?>