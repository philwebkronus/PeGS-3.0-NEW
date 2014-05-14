<?php
/**
 * Active Tickets Monitoring Form
 * @author Mark Kenneth Esguerra
 * @date March 26, 2014
 */
class ActiveTicketsMonitoringForm extends CFormModel
{
    public $sitecode;
    public $datefrom;
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function rules()
    {
        return array(
            array('sitecode', 'required')
        );
    }
}
?>
