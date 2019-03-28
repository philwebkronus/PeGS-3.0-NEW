<?php

/**
 * Date Created 10 28, 11 10:49:36 AM <pre />
 * Description of TerminalMonitoringPager
 * @author Bryan Salazar
 */
class TerminalMonitoringPager {
    /**
     * Pager for terminals
     * @param type $total total number of page
     * @param type $perpage number per page
     * @return string generated html
     */
    public static function display($total,$perpage,$url) {
        $html = '';
        $last = $total;
        $cntr = 1;
        while($total > 0) {
            if($total >= $perpage) {
                $total = $total - $perpage;
                $to = $cntr + $perpage - 1;
            } else {
                $to = $last;
                $total = 0;
            }
            $class = '';
            if(isset($_SESSION['current_page'])) {
                if($_SESSION['current_page'] == $cntr) {
                    $class = 'class="current-page"';
                } 
            } else {
                if($cntr == 1) {
                    $class = 'class="current-page"';
                }
            } 
            $html.='<input id="page'.$cntr.'" ' . $class . ' url="' . Mirage::app()->createUrl($url,array('page'=>$cntr,'same'=>1)) . '" type="button" value="' . $cntr . '-' . $to . '" />';
            $cntr+=$perpage;
        }
//        unset($_SESSION['current_page']);
        $html.='';
        return $html;
    }
    
    
}

