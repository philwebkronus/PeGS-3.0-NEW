<?php
/**
 * Ticket Transactions Monitoring Model
 * @author JunJun S. Hernandez [04-07-14]
 */
class TicketTransactionsMonitoringForm extends CFormModel
{
    public $dateFrom;
    public $dateTo;
    public $site;
    public $vouchertype;
    public $status;
    public $ticketcode;
    
    public static function model($className=__CLASS__)
    {
	return parent::model($className);
    }
    
    public function rules()
    {
        return array(
            array('dateFrom, dateTo, site, vouchertype, status, ticketcode','required'),
        );  
    }
}
?>
