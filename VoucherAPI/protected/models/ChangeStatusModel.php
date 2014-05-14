<?php
/**
 * Change Coupon/Ticket Status Model
 * @author Mark Kenneth Esguerra
 * @date November 4, 2013
 */
class ChangeStatusModel extends CFormModel
{
    public $vouchertype;
    public $batch;
    public $status;
    public $validfrom;
    public $validto;
    public $ticketcode;
    public $currentstat;
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function rules()
    {
        return array(
            array('vouchertype, batch, status, validfrom, 
                   validto, ticketcode, currentstat', 'required')
        );
    }
}
?>
