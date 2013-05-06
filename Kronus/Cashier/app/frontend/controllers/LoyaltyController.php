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
        $result = $loyalty->getCardInfo($_POST['card_number'], false, $_POST['isreg']);
        Mirage::app()->end();
    }

    public function transferPointsAction() {
        if(!$this->isAjaxRequest()) 
            Mirage::app()->error404();
            
        Mirage::loadComponents('LoyaltyAPIWrapper.class');
        $loyalty = new LoyaltyAPIWrapper(); 
        $result = $loyalty->transferPoints($_POST['oldnumber'], $_POST['newnumber'], $_POST['aid'],false);
        Mirage::app()->end();
    }
    
}

