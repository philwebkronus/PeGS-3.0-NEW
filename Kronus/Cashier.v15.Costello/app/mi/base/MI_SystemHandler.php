<?php

/**
 * Date Created 10 27, 11 9:48:46 AM <pre />
 * Description of MI_SystemHandler
 * @author Bryan Salazar
 */
class MI_SystemHandler {
   
   private static $_instance = null;
   
   public static function init() {
      if(!self::$_instance) 
         self::$_instance = new MI_SystemHandler();
      return self::$_instance;
   }
   
   private function MI_SystemHandler() {
//      ini_set('display_errors','On');

      if(MIRAGE_DEBUG) {   
//;   Default Value: E_ALL & ~E_NOTICE
//;   Development Value: E_ALL | E_STRICT
//;   Production Value: E_ALL & ~E_DEPRECATED
//         error_reporting(E_ALL & ~E_DEPRECATED);
//         error_reporting(E_ALL | E_STRICT);
         set_error_handler(array($this,"errorHandler"),error_reporting());
         set_exception_handler(array($this,"exceptionHandler"));
//         register_shutdown_function(array($this,'shutdownHandler'));
      } else {
         error_reporting(E_ALL & ~E_DEPRECATED);
         set_error_handler(array($this,"logPhpError"),error_reporting());
         set_exception_handler(array($this,"logUnCaughtException"));
      }
   }
   
   public function shutdownHandler() {
      $error = error_get_last();
      if($error !== NULL) {
         $this->_displayFatal($error['type'],$error['message'],$error['file'],
            $error['line']);
      }
    }
   
   public function logPhpError($code,$message,$file,$line)
   {
      restore_error_handler();
      restore_exception_handler();
      $errorMessages = '"' . $message . '"' .' File: ' . $file . ' Line: ' . $line;
      MI_Logger::log($errorMessages, $code);
   }

   public function logUnCaughtException($exception)
   {
      restore_error_handler();
      restore_exception_handler();

      $trace = debug_backtrace();
      $last_trace = $trace[0]['args'];

      $ex = $last_trace[0];

      $errorMessages = '"'. $exception->getMessage() . '"' . ' File: ' .
              $ex->getFile() . ' Line: ' . $ex->getLine();
      MI_Logger::log($errorMessages, 'Exception');
   }
   
   public function errorHandler($code,$message,$file,$line)
   {
      restore_error_handler();
      restore_exception_handler();
      $this->displayError($code,$message,$file,$line);
   }
   
   public function exceptionHandler($exception)
   {
      restore_error_handler();
      restore_exception_handler();
      $this->displayException($exception);
   }
   
   private function _displayFatal($code,$message,$file,$line) {

      echo '<h1>Fatal Error</h1>';
      echo '<div style="background-color:#ccc;">' .
           '<h2 style="padding-left:5px;color:red">' .
            $message .
           '</h2>&nbsp;<span>' . $file .
           ' Line: ' . $line .
           '</span></div>';
      $trace = debug_backtrace();
      
      echo '<br /><br /><div><h2>Stack Trace</h2></div>';
			$trace=debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if(count($trace)>3)
				$trace=array_slice($trace,3);
			foreach($trace as $i=>$t)
			{
				if(!isset($t['file']))
					$t['file']='unknown';
				if(!isset($t['line']))
					$t['line']=0;
				if(!isset($t['function']))
					$t['function']='unknown';
				echo "#$i {$t['file']}({$t['line']}): ";
				if(isset($t['object']) && is_object($t['object']))
					echo get_class($t['object']).'->';
				echo "{$t['function']}()<br />";
			}

      echo '</pre>';
      exit(1);
   }
   
   private function displayError($code,$message,$file,$line)
   {
      echo '<h1>PHP Error</h1>';
      echo '<div style="background-color:#ccc;">' .
           '<h2 style="padding-left:5px;color:red">' .
            $message .
           '</h2>&nbsp;<span>' . $file .
           ' Line: ' . $line .
           '</span></div>';
      $trace = debug_backtrace();
      
      echo '<br /><br /><div><h2>Stack Trace</h2></div>';
			$trace=debug_backtrace();
			// skip the first 3 stacks as they do not tell the error position
			if(count($trace)>3)
				$trace=array_slice($trace,3);
			foreach($trace as $i=>$t)
			{
				if(!isset($t['file']))
					$t['file']='unknown';
				if(!isset($t['line']))
					$t['line']=0;
				if(!isset($t['function']))
					$t['function']='unknown';
				echo "#$i {$t['file']}({$t['line']}): ";
				if(isset($t['object']) && is_object($t['object']))
					echo get_class($t['object']).'->';
				echo "{$t['function']}()<br />";
			}

      echo '</pre>';
      exit(1);
   }
   
   private function displayException($exception)
   {
      $exception_name = get_class($exception);
      $trace = $exception->getTrace();
      //$trace = array_reverse($trace);
      $last = count($trace);
      $last_trace = $trace[0];
      if(!isset($last_trace['file']))
         $last_trace['file'] = 'unknown';
      if(!isset($last_trace['line']))
         $last_trace['line'] = 0;

      echo '<h1>' . $exception_name . '</h1>';
      echo '<div style="background-color:#ccc;">' .
           '<h2 style="padding-left:5px;color:red">' .
            $exception->getMessage() .
           '</h2>&nbsp;<span>' . $last_trace['file'] .
           ' Line: ' . $last_trace['line'] .
           '</span></div>';

      echo '<br /><br /><div><h2>Stack Trace</h2></div>';
      //$trace = array_reverse($trace);
			foreach($trace as $i=>$t)
			{
				if(!isset($t['file']))
					$t['file']='unknown';
				if(!isset($t['line']))
					$t['line']=0;
				if(!isset($t['function']))
					$t['function']='unknown';
				echo "#$i {$t['file']}({$t['line']}): ";
				if(isset($t['object']) && is_object($t['object']))
					echo get_class($t['object']).'->';
				echo "{$t['function']}()<br />";
			}

      exit(1);
   }    
}

