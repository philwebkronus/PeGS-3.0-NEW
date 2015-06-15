<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 10 15, 10
 * Company: Philweb
 * *************************** */

class DateSelector extends BaseObject
{

    var $CurrentDateNoFormat;
    var $CurrentDate;
    var $NextDate;
    var $PreviousDate;
    var $DateFormat = "Y-m-d";
    var $TimeZone;
    var $Year;
    var $Month;
    var $Day;

    function DateSelector($whatdate = 'now', $dateformat = "Y-m-d")
    {
        $this->SetDate($whatdate, $dateformat);
    }

    function SetDate($whatdate = 'now', $dateformat = "Y-m-d")
    {
        if (isset($dateformat))
        {
            $this->DateFormat = $dateformat;
        }

        $date = new DateTime($whatdate);
        if (isset($this->TimeZone) && $this->TimeZone != '')
        {
            $date->setTimezone($this->TimeZone);
        }
        $this->CurrentDateNoFormat = $date;
        $this->CurrentDate = $date->format($this->DateFormat);
        $this->Year = $date->format("Y");
        $this->Month = $date->format("m");
        $this->Day = $date->format("d");
        $date->modify("+1 days");
        $this->NextDate = $date->format($this->DateFormat);
        $date->modify("-2 days");
        $this->PreviousDate = $date->format($this->DateFormat);
        $date->modify("+1 days");
    }

    function AddDays($numofdays, $dateformat = "Y-m-d")
    {
        $date = $this->CurrentDateNoFormat;
        $date->modify("+$numofdays days");
        $this->SetDate($date->format($this->DateFormat), $dateformat);
    }

    function AddWeeks($numofweeks, $dateformat = "Y-m-d")
    {
        $date = $this->CurrentDateNoFormat;
        $date->modify("+$numofweeks weeks");
        $this->SetDate($date->format($this->DateFormat), $dateformat);
    }

    function AddMonths($numofmonths, $dateformat = "Y-m-d")
    {
        $date = $this->CurrentDateNoFormat;
        $date->modify("+$numofmonths months");
        $this->SetDate($date->format($this->DateFormat), $dateformat);
    }

    function AddYears($numofyears, $dateformat = "Y-m-d")
    {
        $date = $this->CurrentDateNoFormat;
        $date->modify("+$numofyears years");
        $this->SetDate($date->format($this->DateFormat), $dateformat);
    }

    function GetCurrentDateFormat($dateformat = '')
    {
        if ($dateformat == '')
        {
            $dateformat = $this->DateFormat;
        }

        $date = $this->CurrentDateNoFormat;
        return $date->format($dateformat);
    }

    function GetPreviousDateFormat($dateformat = '')
    {
        if ($dateformat == '')
        {
            $dateformat = $this->DateFormat;
        }
        $dt = $this->CurrentDateNoFormat;
        $date = new DateTime($dt->format("Y-m-d"));
        $date->modify("-1 days");
        return $date->format($dateformat);
    }

    function GetNextDateFormat($dateformat = '')
    {
        if ($dateformat == '')
        {
            $dateformat = $this->DateFormat;
        }
        $dt = $this->CurrentDateNoFormat;
        $date = new DateTime($dt->format("Y-m-d"));
        $date->modify("+1 days");
        return $date->format($dateformat);
    }

    function GetFirstDayOfMonth($dateformat = '')
    {
        if ($dateformat == '')
        {
            $dateformat = $this->DateFormat;
        }
        $dt = $this->CurrentDateNoFormat;
        $date = new DateTime($dt->format("Y-m-") . "01");
        return $date->format($dateformat);
    }

    function GetLastDayOfMonth($dateformat = '')
    {
        if ($dateformat == '')
        {
            $dateformat = $this->DateFormat;
        }
        $dt = $this->CurrentDateNoFormat;
        $date = new DateTime($dt->format("Y-m-t"));
        return $date->format($dateformat);
    }

    function GetNowUSec($withmicroseconds = true)
    {
        $format = 'Y-m-d H:i:s';
        $utimestamp = microtime(true);
        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        $date = new DateTime();

        if (App::getParam("applicationtimezone") != '')
        {
            $date->setTimezone(new DateTimeZone(App::getParam("applicationtimezone")));
        }
        $datestr = $date->format($format);
        if ($withmicroseconds == true)
        {
            $datestr .= '.' . $milliseconds;
        }
        return $datestr;
    }

    function SetTimeZone($timezone)
    {
        $newdate = $this->CurrentDateNoFormat;
        $newdate->setTimeZone(new DateTimeZone($timezone));
        $this->CurrentDateNoFormat = $newdate;
        $this->CurrentDate = $newdate->format($this->DateFormat);
        $newdate->modify("+1 days");
        $this->NextDate = $newdate->format($this->DateFormat);
        $newdate->modify("-2 days");
        $this->PreviousDate = $newdate->format($this->DateFormat);
        $newdate->modify("+1 days");
    }

    function GetTimeZone()
    {
        if (!isset($this->TimeZone) || $this->TimeZone == '')
        {
            $date = $this->CurrentDateNoFormat;
            return $date->getTimezone();
        }
        else
        {
            return $this->TimeZone;
        }
    }

}

?>
