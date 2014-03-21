<?php

/**
 * Class helper for ProgressiveJackpot
 * @package application.modules.launchpad.components
 * @author Bryan Salazar
 */
class ProgressiveJackpot {
    public static function getJockpot($currentCasino,$currentServiceID) {
        
        $url = LPConfig::app()->params['progressive_jackpot'] . $currentServiceID;
        if($currentCasino == 'Vibrant Vegas') {
            return '';
        }
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        $result = curl_exec($ch);
        $return = json_decode($result);
        curl_close($ch);
        $jp = '<ul>';
//        $separator = '';
        if(isset($return->GetProgressiveJackpotByServerIdResult->ProgressiveJackpot[0]->JackpotGame)) {
            foreach($return->GetProgressiveJackpotByServerIdResult->ProgressiveJackpot as $jackpot) {
//                if($jp !='') {
//                    $separator = ' / ';
//                }
//                $jp.= $separator .$jackpot->JackpotGame . ' - ' . number_format($jackpot->JackpotAmount,2) .'  ';
                $jp.='<li>' . $jackpot->JackpotGame . ' - ' . number_format($jackpot->JackpotAmount,2) . '</li>';
            }
            $jp.='</ul>';
            return $jp;
        }
        return '';
        
    }
}
