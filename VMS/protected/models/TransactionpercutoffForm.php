<?php

class TransactionpercutoffForm extends CFormModel
{
    
    public $transactiondate;
    public $site;
    public $vouchertype;
    public $status;
    
    public static function model($className=__CLASS__)
    {
	return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('transactiondate, site, vouchertype, status','required'),
        );  
    }
}
?>
