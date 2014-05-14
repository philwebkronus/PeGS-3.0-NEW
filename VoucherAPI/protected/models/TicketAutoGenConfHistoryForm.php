<?php
/**
 * Ticket Auto-Generation History Form
 */
class TicketAutoGenConfHistoryForm extends CFormModel
{
    public $datefrom;
    public $dateto;
    public $vouchertype;
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function rules()
    {
        return array(
            array('vouchertype, datefrom, dateto', 'required')
        );
    }
}
?>
