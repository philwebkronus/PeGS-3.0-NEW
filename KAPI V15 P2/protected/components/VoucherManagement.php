<?php
/**
 * Method / Action of VMS (Voucher Management System)
 * @author elperez
 */

Yii::import('application.components.checkhost');
Yii::import('application.components.common');

class VoucherManagement {
    
    public $_URI;
    
    /**
     * Generates a voucher this was called in every redemption transaction
     * @param str $trackingID
     * @param int $terminalID
     * @param int $amount
     * @param str $aid
     * @param int $sourceID
     * @param str $token
     * @return str | array
     */
    public function generateVoucher($trackingID, $terminalID, $amount, $aid, $sourceID, $token){
        $this->_URI = Yii::app()->params['generate_voucher'];
        //$sourceID = Yii::app()->params['vms_source'];
        if($this->IsAPIServerOK()){
            $vmsResult = Yii::app()->CURL->run($this->_URI.'?terminalid='.$terminalID.
                                        '&amount='.$amount.'&trackingid='.$trackingID.
                                        '&aid='.$aid.''.'&source='.$sourceID.'&token='.$token);
            if(is_null($vmsResult)){
                return 'Internal Server Error';
            }
            return CJSON::decode($vmsResult);
        }
        else
            return 'Cannot connect to the VMS Host';
    }
    
    /**
     * Verify if voucher is for claiming 
     * This was called in deposit and reload transaction
     * @param str $vouchercode
     * @param int $aid
     * @param str $token
     * @param int $source
     * @param str $trackingId optional
     * @return str | array
     */
    public function verifyVoucher($vouchercode, $aid, $token, $source, $trackingId = ''){
        
        $this->_URI = Yii::app()->params['verify_voucher'];
        
        if($this->IsAPIServerOK()){
            $vmsResult = Yii::app()->CURL->run($this->_URI.'?vouchercode='.$vouchercode.
                                '&aid='.$aid.'&trackingid='.$trackingId.'&token='.$token.
                                '&source='.$source);
            
            if(is_null($vmsResult)){
                
                return 'Internal Server Error';
            }
            return CJSON::decode($vmsResult);
        }
        else
            return 'Cannot connect to the VMS Host';
    }
    
    /**
     * Claims a voucher, this was called in deposit and reload transaction
     * @param int $aid
     * @param str $trackingID
     * @param str $vouchercode
     * @param int $terminalID
     * @param str $token
     * @param int $source
     * @return str | array
     */
    public function useVoucher($aid, $trackingID, $vouchercode, $terminalID, $token, $source){
        $this->_URI = Yii::app()->params['use_voucher'];
        
        //Check if VMS Host is reachable
        if($this->IsAPIServerOK()){
            $vmsResult = Yii::app()->CURL->run($this->_URI.'?aid='.$aid.'&trackingid='.$trackingID.
                                          '&vouchercode='.$vouchercode.'&terminalid='.$terminalID.
                                          '&token='.$token.'&source='.$source);
            if(is_null($vmsResult)){
                return 'Internal Server Error';
            }
            return CJSON::decode($vmsResult);
        }
        else
            return 'Cannot connect to the VMS Host';
    }
    
    /**
     * @todo
     */
    public function cancelVoucher(){
        
    }
    
    /**
     * Updates voucher status from Active/Unclaimed (1) to used(3)
     * @param str $voucherCode
     * @param int $aid
     * @return str | array
     */
    public function updateVoucher($voucherCode, $aid){
        $this->_URI = Yii::app()->params['update_voucher'];
        
        //Check if VMS Host is reachable
        if($this->IsAPIServerOK()){
            $vmsResult = Yii::app()->CURL->run($this->_URI.'?vouchercode='.$voucherCode.'&aid='.$aid.'');
            if(is_null($vmsResult)){
                return 'Internal Server Error';
            }
            return CJSON::decode($vmsResult);
        }
        else
            return 'Cannot connect to the VMS Host';
    }
    
    /**
     * Checks if API endpoint is reachable
     *
     * @param none
     * @return boolean
     */
    public function IsAPIServerOK()
    {
        $port = 80;
        
        $urlInfo = parse_url( $this->_URI );        

        if ( $urlInfo[ 'scheme' ] == 'https' )
        {
            $port = 443;
        }

        return common::isHostReachable( $this->_URI, $port );
    }
}

?>
