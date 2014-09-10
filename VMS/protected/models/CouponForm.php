<?php
/**
 * Coupon Form
 */
class CouponForm extends CFormModel
{
    public $validfrom;
    public $validto;
    public $generatedfrom;
    public $generatedto;
    public $transdatefrom;
    public $transdateto;
    
    public static function model($classname = __CLASS__)
    {
        return parent::model($classname);
    }
}
?>
