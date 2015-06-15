<?php

require_once("include/core/init.inc.php");

App::LoadModuleClass("Dashboard", "DashboardEvents");

class Calendar {

    var $date;
    var $year;
    var $month;
    var $day;
    var $week_start_on = FALSE;
    var $week_start = 7; // sunday
    var $link_days = TRUE;
    var $link_to;
    var $formatted_link_to;
    var $mark_today = TRUE;
    var $today_date_class = 'today';
    var $mark_selected = TRUE;
    var $selected_date_class = 'selected';
    var $mark_passed = TRUE;
    var $passed_date_class = 'passed';
    var $highlighted_dates;
    var $default_highlighted_class = 'highlighted';
    var $prev;
    var $next;
    var $lasty;
    var $nexty;
    var $theprevyear;
    var $thenextyear;

    function Calendar($date = NULL, $year = NULL, $month = NULL) {
        $self = htmlspecialchars($_SERVER['PHP_SELF']);
        $this->link_to = $self;

        if (is_null($year) || is_null($month)) {
            if (!is_null($date)) {
                $this->date = date("Y-m-d", strtotime($date));
            } else {
                $this->date = date("Y-m-d");
            }
            $this->set_date_parts_from_date($this->date);
        } else {
            $this->year = $year;
            $this->month = str_pad($month, 2, '0', STR_PAD_LEFT);
        }
    }

    function set_date_parts_from_date($date) {
        $this->year = date("Y", strtotime($date));
        $this->month = date("m", strtotime($date));
        $this->day = date("d", strtotime($date));
    }

    function day_of_week($date) {
        $day_of_week = date("N", $date);
        if (!is_numeric($day_of_week)) {
            $day_of_week = date("w", $date);
            if ($day_of_week == 0) {
                $day_of_week = 7;
            }
        }
        return $day_of_week;
    }

    function output_calendar($year = NULL, $month = NULL, $calendar_class = 'calendar') {

        if ($this->week_start_on !== FALSE) {
            echo "The property week_start_on is replaced due to a bug present in version before 2.6. of this class! Use the property week_start instead!";
            exit;
        }

        $year = ( is_null($year) ) ? $this->year : $year;
        if (isset($_GET['year']))
            $year = $_GET['year'];

        $page_year = $year;

        $month = ( is_null($month) ) ? $this->month : str_pad($month, 2, '0', STR_PAD_LEFT);

        if (isset($_GET['month']))
            $month = $_GET['month'];

        $month_start_date = strtotime($year . "-" . $month . "-01");
        $first_day_falls_on = $this->day_of_week($month_start_date);
        $days_in_month = date("t", $month_start_date);
        $month_end_date = strtotime($year . "-" . $month . "-" . $days_in_month);
        $start_week_offset = $first_day_falls_on - $this->week_start;
        $prepend = ( $start_week_offset < 0 ) ? 7 - abs($start_week_offset) : $first_day_falls_on - $this->week_start;
        $last_day_falls_on = $this->day_of_week($month_end_date);

        $prev = $month - 1;
        $next = $month + 1;
        $lasty = $year - 1;
        $nexty = $year + 1;
        $theyear = $page_year;
        $theprevyear = $theyear;
        $thenextyear = $theyear;
        if ($prev == 0) {
            $prev = 12;
            $theprevyear = $theyear - 1;
        }
        if ($next == 13) {
            $next = 1;
            $thenextyear = $theyear + 1;
        }

        if (strlen($prev) == 1)
            $prev = '0' . $prev;
        if (strlen($next) == 1)
            $next = '0' . $next;

        $output .= "<table id=\"calendar1\" class=\"thirdpadded\">\n";
        $output .= "<tr class=\"monthheader\">";
        $output .= "<th>";
        $output .= "<a class=\"navi\" href='event_calendar.php?month=$month&year=$lasty'>&lt;&lt; $lasty</a>&nbsp;&nbsp;";
        $output .= "</th>";
        $output .= "<th>";
        $output .= "<a class=\"navi\" href='event_calendar.php?month=$prev&year=$theprevyear'>&lt; Prev</a>";
        $output .= "</th>";
        $output .= "<th colspan=\"3\">" . ucfirst(strftime("%B %Y", $month_start_date)) . "</th>";
        $output .= "<th>";
        $output .= "<a class=\"navi\" href='event_calendar.php?month=$next&year=$thenextyear'>Next &gt;</a>&nbsp;&nbsp;";
        $output .= "</th>";
        $output .= "<th>";
        $output .= "<a class=\"navi\" href='event_calendar.php?month=$month&year=$nexty'>$nexty &gt;&gt;</a>";
        $output .= "</th>";
        $output .= "</tr>";

        $col = '';
        $th = '';
        for ($i = 1, $j = $this->week_start, $t = (3 + $this->week_start) * 86400; $i <= 7; $i++, $j++, $t+=86400) {
            $localized_day_name = gmstrftime('%A', $t);
            $col .= "<col class=\"" . strtolower($localized_day_name) . "\" />\n";
            $th .= "\t<td class=\"dayheader\" title=\"" . ucfirst($localized_day_name) . "\">" . ucfirst($localized_day_name) . "</td>\n";
            $j = ( $j == 7 ) ? 0 : $j;
        }

        $output .= $col;
        $output .= "<tr>\n";
        $output .= $th;
        $output .= "</tr>\n";
        $output .= "<tbody>\n";
        $output .= "<tr>\n";
        $weeks = 1;

        for ($i = 1; $i <= $prepend; $i++) {
            $output .= "\t<td class='calendarcell'>&nbsp;</td>\n";
        }

        $event_count_cache = array();
        for ($day = 1, $cell = $prepend + 1; $day <= $days_in_month; $day++, $cell++) {

            if (!isset($event_count_cache[$day + 0])) {
                $event_count_cache[$day + 0] = 0;
            }
            if ($cell == 1 && $day != 1) {
                $output .= "<tr>\n";
            }

            $day = str_pad($day, 2, '0', STR_PAD_LEFT);
            $day_date = $year . "-" . $month . "-" . $day;

            $dbEvt = new DashboardEvents();
            $arrRes = $dbEvt->SelectEvent(date('Y-m', strtotime($day_date)));
//            $countev = count($arrRes);

            if ($this->mark_today == TRUE && $day_date == date("Y-m-d")) {
                $classes[] = $this->today_date_class;
            }

            if ($this->mark_selected == TRUE && $day_date == $this->date) {
                $classes[] = $this->selected_date_class;
            }

            if ($this->mark_passed == TRUE && $day_date < date("Y-m-d")) {
                $classes[] = $this->passed_date_class;
            }

            if (is_array($this->highlighted_dates)) {
                if (in_array($day_date, $this->highlighted_dates)) {
                    $classes[] = $this->default_highlighted_class;
                }
            }

            if (isset($classes)) {
                $day_class = ' class="';
                foreach ($classes AS $value) {
                    $day_class .= $value . " ";
                }
                $day_class = substr($day_class, 0, -1) . '"';
            } else {
                $day_class = '';
            }

            $title_format = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? "%A, %B %d, %Y" : "%A, %B %e, %Y";

            $output .= "\t<td class='calendarcell' title=\"" . ucwords(strftime($title_format, strtotime($day_date))) . "\">";

            unset($day_class, $classes);

            switch ($this->link_days) {
                case 0 :
                    $output .= $day;
                    break;

                case 1 :
                    if (empty($this->formatted_link_to)) {
                        $hasEvent2 = false;
                        if (!is_null($arrRes)) {
                            
                            
                            
                            foreach ($arrRes as $value) {
                                $date_time = explode(' ', $value['datefrom']);
                                $d = explode('-', $date_time[0]);
                                if ($d[2] == $day) {
                                    $date_to = $value['dateto'] + 0;
                                    $temp_day = $day + 0;
                                    while ($temp_day <= $date_to) {
                                        $event_count_cache[$temp_day]++;
                                        $temp_day++;
                                    }
                                    
//                                    $output .= "<span class='calendardateon'>" . $day . "</span>";
                                    $hasEvent2 = true;
                                    break;
                                }
                            }
                            if($event_count_cache[$day+0] != 0) {
                                $output .= "<span class='calendardateon'>" . $day . "</span>";
                            } else {
                                $output .= "<span class='calendardate'>" . $day . "</span>";
                            }
                            
//                            if (!$hasEvent2) {
//                                $output .= "<span class='calendardate'>" . $day . "</span>";
//                                $hasEvent2 = false;
//                            }
                        } else {
                            $output .= "<span class='calendardate'>" . $day . "</span>";
                        }

                        $hasEvent = false;
                        if (!is_null($arrRes)) {
                            foreach ($arrRes as $value) {
                                

                                $date_time = explode(' ', $value['datefrom']);
                                $d = explode('-', $date_time[0]);
                                if ($d[2] == $day) {



                                    $hasEvent = true;
                                    $eventcount = 1;
                                    break;
                                } else {
                                    $hasEvent = false;
                                    $eventcount = 0;
                                }
                            }
                            $output .= "<div class=\"calday\">";
                            if (!isset($event_count_cache[$day]) || $event_count_cache[$day] == null) {
                                $event_count_cache[$day] = 0;
                            }
                            $output .= "<center><a class=\"calevent\" href=\"" . $this->link_to . "?date=" . $day_date . "&month=" . $month . "&year=" . $year . " \"> Events: " . $event_count_cache[$day + 0] . "</a></center>";
                            $output .= "</div>";
                        } else {
                            $output .= "<div class=\"calday\">";
                            $output .= "<center><a class=\"calevent\" href=\"" . $this->link_to . "?date=" . $day_date . "&month=" . $month . "&year=" . $year . " \"> Events: " . $event_count_cache[$day + 0] . "</a></center>";
                            //$output .= $value['event'];
                            $output .= "</div>";
                        }
                    } else {
                        $output .= "<a class=\"calevent\" href=\"" . strftime($this->formatted_link_to, strtotime($day_date)) . "\">" . $day . "</a>";
                    }
                    break;

                case 2 :
                    if (is_array($this->highlighted_dates)) {
                        if (in_array($day_date, $this->highlighted_dates)) {
                            if (empty($this->formatted_link_to)) {
                                $output .= "<a href=\"" . $this->link_to . "?date=" . $day_date . "\">";
                                foreach ($arrRes as $value) {
                                    $date_time = explode(' ', $value['datefrom']);
                                    $d = explode('-', $date_time[0]);
                                    if ($d[2] == $day) {
                                        $output .= "<div>";
                                        $output .= $value['event'];
                                        $output .= "</div>";
                                    }
                                }
                            } else {
                                $output .= "<a class=\"calevent\" href=\"" . strftime($this->formatted_link_to, strtotime($day_date)) . "\">";
                            }
                        }
                    }
                    $output .= $day;
                    if (is_array($this->highlighted_dates)) {
                        if (in_array($day_date, $this->highlighted_dates)) {
                            if (empty($this->formatted_link_to)) {
                                $output .= "</a>";
                            } else {
                                $output .= "</a>";
                            }
                        }
                    }
                    break;
            }

            $output .= "</td>\n";

            if ($cell == 7) {
                $output .= "</tr>\n";
                $cell = 0;
            }
        }

        if ($cell > 1) {
            for ($i = $cell; $i <= 7; $i++) {
                $output .= "\t<td class=\"pad\">&nbsp;</td>\n";
            }
            $output .= "</tr>\n";
        }

        $output .= "</tbody>\n";
        $output .= "</table>\n";

        if (isset($_GET['date']))
            $day_date = $_GET['date'];
        $output .= "<input type='hidden' readonly value='$day_date'>\n";

        return $output;
    }

    function returnDate() {
        $day_date = $_GET['date'];
        return $day_date;
    }

}

?>