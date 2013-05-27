<?php
Mirage::loadComponents('FrontendController');
/**
 * Date Created 11 15, 11 6:20:07 PM <pre />
 * Description of LoyaltyController
 * @author Bryan Salazar
 */
class LoyaltyController extends FrontendController {
    
    public function cardInquiryAction() {
        if(!$this->isAjaxRequest()) 
            Mirage::app()->error404();
            
        Mirage::loadComponents('LoyaltyAPIWrapper.class');
        $loyalty = new LoyaltyAPIWrapper(); 
        if(isset($_POST['card_number']) && isset($_POST['isreg'])){
            $cardnumber = $_POST['card_number'];
            $isreg = $_POST['isreg'];
            $result = $loyalty->getCardInfo($cardnumber, false, $isreg);
        }
        Mirage::app()->end();
    }

    public function transferPointsAction() {
        if(!$this->isAjaxRequest()) 
            Mirage::app()->error404();
            
        Mirage::loadComponents('LoyaltyAPIWrapper.class');
        $loyalty = new LoyaltyAPIWrapper(); 
        if(isset($_POST['oldnumber']) && isset($_POST['newnumber']) && isset($_POST['aid'])){
            $oldnumber = $_POST['oldnumber'];
            $newnumber = $_POST['newnumber'];
            $aid = $_POST['aid'];
            $result = $loyalty->transferPoints($oldnumber , $newnumber, $aid,false);
        }
        
        Mirage::app()->end();
    }
    
}

