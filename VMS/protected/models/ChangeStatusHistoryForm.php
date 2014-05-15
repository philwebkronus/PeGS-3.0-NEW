<?php

class ChangeStatusHistoryForm extends CFormModel
{
    public $vouchertype;
    public $vouchercode;
    public $datefrom;
    public $dateto;
    public $hdn_vouchertype;
    public $hdn_vouchercode;
    public $hdn_datefrom;
    public $hdn_dateto;
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function rules()
    {
        return array(
            array('vouchertype, vouchercode, datefrom, dateto', 'required')
        );
    }
}
?>
