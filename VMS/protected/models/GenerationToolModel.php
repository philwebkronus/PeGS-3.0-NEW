<?php
/**
 * Coupon/Ticket Generation Tool Model
 * @author Mark Kenneth Esguerra
 * @date October 30, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class GenerationToolModel extends CFormModel
{
    public $vouchertype;
    public $count;
    public $amount;
    public $distributiontag;
    public $iscreditable;
    public $couponbatch;
    public $remainingcount;
    public $thresholdlimit;
    public $autoticketcount;
    public $autogenjob;
    public $hdn_autogenjob;
    public $hdn_threshold;
    public $hdn_ticketcount;
    
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    public function rules()
    {
        return array(
            array('vouchertype, count, amount, distributiontag, 
                   iscreditable', 'required'),
            array('amount', 'length', 'max' => 6),
            array('couponbatch, remainingcount', 'safe')
        );
    }
    
    /**
    * @Description: Generate Alphanumeric combination for security code
    * @link http://php.net/manual/en/function.mt-rand.php
    * @param int $length
    * @return string
    */
   public function mt_rand_str ($length) {
       
       $c = str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
       $s = '';
       $cl = strlen($c)-1;
       for ($cl = strlen($c)-1, $i = 0; $i < $length; $s .= $c[mt_rand(0, $cl)], ++$i);
       return $s;
   }
}
?>
