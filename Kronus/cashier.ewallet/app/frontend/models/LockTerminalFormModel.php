<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LockTerminalFormModel
 *
 * @author jdlachica
 */
class LockTerminalFormModel extends MI_Model {
    public $amount;
    public $sel_amount;
    public $casino = 20;
    public $loyalty_card;
    public $terminal_id;
    public $max_deposit;
    public $min_deposit;
    public $voucher_code;
    
    protected static $_custom_validation = null;


    protected function _validation() {
        if(self::$_custom_validation == null) {
            self::$_custom_validation = array(
                array('fields'=>array('amount','casino','terminal_id'),'validator'=>'StringValidator'),
                array('fields'=>array('amount'),'validator'=>'NumberValidator',
                        'options'=>array(
                            'min'=>SiteDenominationModel::$min,
                            'max'=>SiteDenominationModel::$max,
                            'divisible'=>100
                        )
                    ),
            );
        }
        return self::$_custom_validation;
    }
    
    public function isValid($attributes,$is_redeem = false) {
        Mirage::loadModels('SiteDenominationModel');
        if(in_array('amount', $attributes)) {
            if($this->sel_amount && $this->sel_amount != '--') {
                $this->amount = $this->sel_amount;
            }
            $this->amount = toInt($this->amount);
            if($is_redeem == false) {
                if($this->getSiteBalance() < $this->amount) {
                    $this->throwError('Not enough BCF');
                }
            } else {
                // PURPOSE: unset divisble so that it will not validate in redeem
                $this->_validation();
                unset(self::$_custom_validation[1]['options']['divisible']);
//                debug(self::$_custom_validation); exit;
            }

        }
        
        return parent::isValid($attributes);
    }
    
    public function getSiteBalance() {
        Mirage::loadModels('SiteBalanceModel');
        $sitebalanceModel = new SiteBalanceModel();
        $site_balance = $sitebalanceModel->getSiteBalance($_SESSION['AccountSiteID']);
        return toInt($site_balance['Balance']);
    }
    
        protected function throwError($message) {
        header('HTTP/1.0 404 Not Found');
        echo $message;
        Mirage::app()->end();
    }
}
