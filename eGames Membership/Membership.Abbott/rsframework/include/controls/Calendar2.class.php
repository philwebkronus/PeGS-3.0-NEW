<?php

/*
 * Author: Roger Sanchez
 * Date Created: 2011-09-14
 * Company: Philweb
 */

class Calendar2 extends BaseControl
{

    public $CurrentDate;
    private $Month;
    private $MonthString;
    private $Year;
    private $FirstDay;
    private $LastDay;
    private $CalendarDays;
    private $DataSource;
    private $SelectedDates;

    function Calendar2($currentdate = "now")
    {
        $ds = new DateSelector($currentdate);
        $month = $ds->GetNextDateFormat("m");
        $monthstring = $ds->GetNextDateFormat("F");
        $year = $ds->GetNextDateFormat("Y");
        $firstday = date("w", "$year-$month-01");
        $lastday = date("t", "$year-$month-01");
        $week = 0;
        $day = $firstday;
        $cal = "";

        $this->Month = $month;
        $this->MonthString = $monthstring;
        $this->Year = $year;
        $this->FirstDay = $firstday;
        $this->LastDay = $lastday;
        $this->CurrentDate = $ds->CurrentDate;
        

        for ($i = 1; $i <= $lastday; $i++)
        {
            $cal[$week][$day] = $i;
            if ($day == 6)
            {
                $day = 0;
                $week++;
            }
            else
            {
                $day++;
            }
        }
        $this->CalendarDays = $cal;
    }

    function Render()
    {
        $cal = $this->CalendarDays;
        
        $calendarstring = "

<link rel='stylesheet' type='text/css' href='css/calendar.css' />
    

";
        $calendarstring .= "<table id=\"calendar\" class=\"thirdpadded\">";
        $calendarstring .= "    <tr class='monthheader'>";
        $calendarstring .= "        <th>&lt; Prev</th><th colspan=\"5\">";
        $calendarstring .= "            $this->MonthString";
        $calendarstring .= "        </th><th>Next &gt;</th>";
        $calendarstring .= "    </tr>";
        $calendarstring .= "    <tr>";
        $calendarstring .= "        <td class='dayheader'>Sun</td><td class='dayheader'>Mon</td><td class='dayheader'>Tue</td><td class='dayheader'>Wed</td><td class='dayheader'>Thu</td><td class='dayheader'>Fri</td><td class='dayheader'>Sat</td>";
        $calendarstring .= "    </tr>";

        foreach ($cal as $key => $val)
        {

            $calendarstring .= "    <tr>";

            for ($dayofweek = 0; $dayofweek < 7; $dayofweek++)
            {
                $day = $val[$dayofweek];

                //$calendarstring .= "<td class='calendarcell'>";
                $cellstyle = "calendarcell";
                if ($day == 11 || $day == '07' || $day == '16' || $day == '05' || $day == '04' || $day == '29')
                {
                    $cellstyle = "calendarcellon";
                }
                
                $curdate = $this->Year . "-" . $this->Month . "-" . $day;
                if ($curdate == $this->CurrentDate)
                {
                    $cellstyle .= " calendarcellcurrentdate";
                }
                
                $calendarstring .= "<td class='$cellstyle'>";
                
                $cssclass = "calendardate";
                if ($day == 11 || $day == '07' || $day == '16' || $day == '05' || $day == '04' || $day == '29')
                {
                    $calendarstring .= "<span class=\"calendardateon\">$day</span>";
                }
                else
                {
                    $calendarstring .= "<span class=\"calendardate\">$day</span>";
                }   
                if ($day)
                {
                $calendarstring .= "<div class=center>Events: 0</div>"; 
                }
                $calendarstring .= "</td>";
            }
            $calendarstring .= "</tr>";
        }
        $calendarstring .= "</table>";
        
        return $calendarstring;
    }

}

?>
