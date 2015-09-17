<?php

/**
 * This class is used for error logging in replacement of the original
 * CFileLogRoute Class
 *
 * @author Marx - Lenin C. Topico
 * @modified by Edson L. Perez
 * @version 1.0
 * @package application.components
 */
class CFileLogRouteModified extends CLogRoute {
    
    /**
         * @var integer maximum log file size
         */
        private $_maxFileSize=1; // in KB
        /**
         * @var integer number of log files used for rotation
         */
        private $_maxLogFiles=5;
        /**
         * @var string directory storing log files
         */
        private $_logPath;
        /**
         * @var string log file name
         */
        private $_logFile = ".log";

        /**
         * Initializes the route.
         * This method is invoked after the route is created by the route manager.
         */
        public function init()
        {
                parent::init();
                if($this->getLogPath()===null)
                        $this->setLogPath(Yii::app()->getRuntimePath());
                
                $this->_logFile = date("Y_m_d").$this->_logFile;
                
        }

        /**
         * @return string directory storing log files. Defaults to application runtime path.
         */
        public function getLogPath()
        {
                return $this->_logPath;
        }

        /**
         * @param string $value directory for storing log files.
         * @throws CException if the path is invalid
         */
        public function setLogPath($value)
        {
                $this->_logPath=realpath($value);
                if($this->_logPath===false || !is_dir($this->_logPath) || !is_writable($this->_logPath))
                        throw new CException(Yii::t('yii','CFileLogRoute.logPath "{path}" does not point to a valid directory. Make sure the directory exists and is writable by the Web server process.',
                                array('{path}'=>$value)));
        }

        /**
         * @return string log file name. Defaults to 'application.log'.
         */
        public function getLogFile()
        {
                return $this->_logFile;
        }

        /**
         * @param string $value log file name
         */
        public function setLogFile($value)
        {
                $this->_logFile=$value;
        }

        /**
         * @return integer maximum log file size in kilo-bytes (KB). Defaults to 1024 (1MB).
         */
        public function getMaxFileSize()
        {
                return $this->_maxFileSize;
        }

        /**
         * @param integer $value maximum log file size in kilo-bytes (KB).
         */
        public function setMaxFileSize($value)
        {
                if(($this->_maxFileSize=(int)$value)<1)
                        $this->_maxFileSize=1;
        }

        /**
         * @return integer number of files used for rotation. Defaults to 5.
         */
        public function getMaxLogFiles()
        {
                return $this->_maxLogFiles;
        }

        /**
         * @param integer $value number of files used for rotation.
         */
        public function setMaxLogFiles($value)
        {
                if(($this->_maxLogFiles=(int)$value)<1)
                        $this->_maxLogFiles=1;
        }

        /**
         * Saves log messages in files.
         * @param array $logs list of log messages
         */
        protected function processLogs($logs)
        {
                $logFile=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
                if(@filesize($logFile)>$this->getMaxFileSize()*1024)
                        $this->rotateFiles();
                $fp=@fopen($logFile,'a');
                @flock($fp,LOCK_EX);
                foreach($logs as $log)
                        @fwrite($fp,$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]));
                @flock($fp,LOCK_UN);
                @fclose($fp);
        }

        /**
         * Rotates log files.
         */
        protected function rotateFiles()
        {
            
                $file=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
                
                $serverTime = date("Hi");
                
                $date = date("Y_m_d");
                
                if($serverTime == "0001") {

                    if(is_file($file)) {
                        
                        @rename($file, $date.$file); // suppress errors because it's possible multiple processes enter into this section
         
                    }
                    
                }
        }
    
}

?>
