<?php
/**
 * Description: easy to read vardump
 * @param string $var 
 */
function debug($var) {
    CVarDumper::dump($var,100,true);
}

function logDebug($var) {
    MI_Logger::log(CVarDumper::dumpAsString($var, 100, false), 'DEBUG');
}

function logger($message,$type=E_ERROR,$casino='') {
    $trace = debug_backtrace();

    $last_trace = $trace[0];
    if(!isset($last_trace['file']))
        $last_trace['file'] = 'unknown';
    if(!isset($last_trace['line']))
        $last_trace['line'] = 0;

    if($casino == ''){
        if(isset($_SESSION['site_code'])) {
            $message = "\"$message\"" . ' SiteID = ' . $_SESSION['AccountSiteID'] . ' File: ' . $last_trace['file'] . ' LINE: ' . $last_trace['line'];
        } else {
            $message = "\"$message\"" . ' File: ' . $last_trace['file'] . ' LINE: ' . $last_trace['line'];
        }
        MI_Logger::log($message, $type);
    } else {
        $message = $message;  
        MI_Logger::casinolog($message, $type, false, $casino);
    }
    
}

/**
 * Description: convert int to money format
 * @param int $value
 * @return string 
 */
function toMoney($value) {
    if($value !== '')
        return number_format($value,2);
}

/**
 * Description: convert money format to int
 * @param int $value
 * @return int 
 */
function toInt($value) {
    if($value)
        return str_replace(',', '', $value);
}

function getTimePlaying($minutes) {
    $hours = 0;
    if($minutes > 60) {
        $hours = floor($minutes/60);
        $minutes = $minutes - ($hours*60);
    } else {
        $hours = "0";
    }
    if($minutes<10) {$minutes = "0$minutes";}
    return "$hours:$minutes";
}

/**
 * Description: Add one day to date with a format of date('Y-m-d');
 * @param type $date format is still the same
 */
function addOneDay($date) {
    $exp_date = explode('-', $date);
    $enddate = date('Y-m-d',mktime(0,0,0,$exp_date[1],$exp_date[2] + 1,
              $exp_date[0]));
    return $enddate;
}

function playingDuration($date_time) {
    $date1 = $date_time;
    $date2 = date('Y-m-d H:i:s');   

    $diff = abs(strtotime($date2) - strtotime($date1));
    $days = floor($diff / (60*60*24));
    $hours = floor(($diff - $days * 60*60*24) / (60 * 60));
    $mins = floor(($diff - $days * 60*60*24  - $hours * 60 * 60) / 60);
    
    
    if($days < 2) {
        $days = $days . ' day, ';
    } else {
        $days = $days . ' days, ';
    }
    
    if($hours < 2) {
        $hours = $hours . ' hr and ';
    } else {
        $hours = $hours . ' hrs and ';
    }
    
    return $days . $hours .  $mins . ' min';        
}
/**
 * Description: Javascript use in time that use server side time
 *  for initial value
 * @param type $id id of container of time
 * @return string script to move the time w/o the sript tag
 */
function clock($id) {
    $startdate = date("F d, Y H:i:s");
    $script = 
        '
            var timesetter = new Date(\''. $startdate . '\');
            var TimeNow = \'\';
            function MakeTime() {
            timesetter.setTime(timesetter.getTime()+1000);
            var hhN  = timesetter.getHours();
            if(hhN > 12){
            var hh = String(hhN - 12);
            var AP = \'PM\';
            } else if(hhN == 12) {
            var hh = \'12\';
            var AP = \'PM\';
            }else if(hhN == 0){
            var hh = \'12\';
            var AP = \'AM\';
            }else{
            var hh = String(hhN);
            var AP = \'AM\';
            }
            var mm  = String(timesetter.getMinutes());
            var ss  = String(timesetter.getSeconds());
            TimeNow = ((hh < 10) ? \' \' : \'\') + hh + ((mm < 10) ? \':0\' : \':\') + mm + ((ss < 10) ? \':0\' : \':\') + ss + \' \' + AP;
            //alert(TimeNow); return false;
            jQuery(\'#'.$id.'\').html(TimeNow);
            //document.getElementById("' . $id.'").firstChild.nodeValue = TimeNow;
            setTimeout(function(){MakeTime();},1000);
            ' .
            '  }
            MakeTime(); 
        ';
    return $script;
}

function isIEBrowser() {
    $agent = (isset($_SERVER['HTTP_USER_AGENT']))?$_SERVER['HTTP_USER_AGENT'] : '';

    if((stripos($agent, 'msie') !== false) && (stripos($agent, 'opera') === false))
        return true;
    return false;
}

function getIEVersion() {
    $match=preg_match('/MSIE ([0-9]\.[0-9])/',$_SERVER['HTTP_USER_AGENT'],$reg);
    if($match==0)
        return -1;
    else
        return floatval($reg[1]);
}

/**
 * Description: Minus one to date 
 * @param type $date format is still the same
 * @return date
 */
function minusOneDay($date){
    $exp_date = explode('-', $date);
    $enddate = date('Y-m-d',mktime(0,0,0,$exp_date[1],$exp_date[2] - 1,
              $exp_date[0]));
    return $enddate;
}