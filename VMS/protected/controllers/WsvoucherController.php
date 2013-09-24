<?php

/**
 * Description of WsVoucherController
 *
 * @author elperez
 */
class WsvoucherController extends Controller{
    
    //Source came from KAPI 
    CONST SOURCE_KAPI = 1;
    
    //Source came from EGM
    CONST SOURCE_EGM = 2;
    
    //Source came from Kronus Cashier
    CONST SOURCE_CASHIER = 3;
    
    CONST CASH_TYPE_BILL = 3;
    CONST VOUCHER_USED_STATUS = 3;
    
    CONST TICKET = 1;
    CONST COUPON = 2;
    
    /**
     * @author Edson Perez
     * @datecreated 09/12/13
     * @purpose verify whether a coupon, ticket and tracking id is valid and can be used to transact
     */
    public function actionVerify(){
         Yii::import('application.controllers.*');

        $commonController = new CommonController();
                    
        if(isset($_GET['aid']) && is_numeric($_GET['aid'])
           && isset($_GET['source']) && is_numeric($_GET['source'])) {           
            $AID = trim($_GET['aid']);
            $source = trim($_GET['source']);
            $trackingid = "";
            $voucherCode = "";
            $dateCreated = "";
            $loyaltyCreditable = 0;
            $amount = 0;
            $result = array();
            $status = 0;
            
            
            //will be called for validation of coupons
            if(isset($_GET['vouchercode']) && ctype_alnum($_GET['vouchercode']) && strlen($_GET['vouchercode']) > 0){
                $voucherCode = trim($_GET['vouchercode']);
                
                   switch ($source){
                        case self::SOURCE_EGM:
                            //todo
                            break;
                        case self::SOURCE_CASHIER:
                                  $result = $commonController->verifyCoupon($AID, $voucherCode, 
                                                $source, $trackingid, self::COUPON);
                                  $transMsg = $result['TransMsg'];
                            break;
                        case self::SOURCE_KAPI;
                            //todo
                            break;
                        default :
                                $errorCode = 2;
                                $transMsg = "Source is invalid.";
                                Utilities::log("Error Message: ".$transMsg." ErrorCode: ".$errorCode); 
                                $result = $commonController->getVerifyRetMsg(2, $errorCode, 
                                                $transMsg, $voucherCode, $amount, $dateCreated, 
                                                $loyaltyCreditable);
                        break;
                }
           } 
           
           //will be called for verification / fulfillment of transaction
           elseif(isset($_GET['trackingid']) && ctype_alnum($_GET['trackingid']) && 
                    strlen($_GET['trackingid']) > 0 ){
                
                $trackingid = trim($_GET['trackingid']);
                
                switch ($source){
                    case self::SOURCE_EGM:
                        //todo
                        break;
                    case self::SOURCE_CASHIER:
                        $_couponModel = new CouponModel();
                        
                        //verify the tracking id
                        $trackResult = $_couponModel->verifyCouponTransaction($trackingid);
                        if((int)$trackResult['ctrtracking'] > 0){
                            $errorCode = 0;
                            $status = 1;
                            $transMsg = "Transaction Approved";
                            $loyaltyCreditable = $trackResult['LoyaltyCreditable'];
                            $result = $commonController->getVerifyRetMsg(2, $errorCode, 
                                            $transMsg, $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                        } else {
                            $errorCode = 1;
                            $transMsg = "Tracking ID not found.";
                            Utilities::log("Error Message: ".$transMsg." ErrorCode: ".$errorCode); 
                            $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, 
                                            $voucherCode, $amount, $dateCreated, $loyaltyCreditable);
                        }
                        break;
                    case self::SOURCE_KAPI;
                        //todo
                    default :
                            $errorCode = 2;
                            $transMsg = "Source is invalid.";
                            Utilities::log("Error Message: ".$transMsg." ErrorCode: ".$errorCode); 
                            $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, 
                                            $voucherCode, $amount, $dateCreated);
                        break;
                }
                
                $details = "VerifyVoucher : ".$transMsg;
                AuditLog::logAPITransactions(1, $source, $details, $voucherCode, $trackingid, $status);
            } else {
                 $transMsg = 'Invalid input parameters.';
                 $errorCode = 3;
                 Utilities::log("Error Message: ".$transMsg." ErrorCode: ".$errorCode);                            
                 $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, 
                                    $voucherCode, $amount, $dateCreated,$loyaltyCreditable);
            }
            
        } else {
            $transMsg = 'Invalid input parameters.';
            $errorCode = 3;
            Utilities::log("Error Message: ".$transMsg." ErrorCode: ".$errorCode);                            
            $result = $commonController->getVerifyRetMsg(2, $errorCode, $transMsg, 
                            $voucherCode, $amount, $dateCreated,$loyaltyCreditable);
        }
        
        $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>$result)));
    }
    
    /**
     * @author Edson Perez
     * @datecreated 09-16-13
     * @purpose generic api method of using whether coupon or ticket
     */
    public function actionUse(){
        Yii::import('application.controllers.*');
        $commonController = new CommonController();
                    
        if(isset($_GET["vouchercode"]) && ctype_alnum($_GET["vouchercode"])
            && isset($_GET["aid"]) && is_numeric($_GET["aid"])
            && isset($_GET['trackingid']) && ctype_alnum($_GET['trackingid'])
            && isset($_GET['terminalid']) && is_numeric($_GET['terminalid'])
            && isset($_GET['source']) && is_numeric($_GET['source']))
        {
            $voucherCode = trim($_GET["vouchercode"]);
            $AID = trim($_GET["aid"]);
            $trackingID = trim($_GET['trackingid']);
            $terminalID = trim($_GET['terminalid']);
            $source = trim($_GET['source']);
            
            switch ($source){
                case self::SOURCE_EGM:
                        //todo
                    break;
                case self::SOURCE_CASHIER:
                    $result = $commonController->useCoupon($voucherCode, $AID,$trackingID,
                            $terminalID, $source, self::COUPON);
                    break;
                case self::SOURCE_KAPI;
                    //todo
                    break;
                default :
                    $errorCode = 2;
                    $transMsg = "Source is invalid.";
                    Utilities::log("Error Message: ".$transMsg." ErrorCode: ".$errorCode); 
                    $result = $commonController->getUseRetMsg(2, $transMsg, $errorCode);
                    break;
            }
            
        } else {
                       
            $transMsg = 'Invalid input parameters.';
            $errorCode = 3;
            Utilities::log("Error Message: ".$transMsg." ErrorCode: ".$errorCode);     
            $result = $commonController->getUseRetMsg(2, $transMsg, $errorCode);
        }
        
        $this->_sendResponse(200, CJSON::encode(array("UseVoucher"=>$result)));
        
    }
    
    /**
     * @todo
     * @return type
     */
    private function _readJsonRequest(){
        
         //read the post input (use this technique if you have no post variable name):
        $post = file_get_contents("php://input");
        
        //decode json post input as php array:
        $data = CJSON::decode($post, true);
        
        return $data;
    }
    
    /**
     *
     * @param type $status
     * @param string $body
     * @param type $content_type 
     * @link http://www.yiiframework.com/wiki/175/how-to-create-a-rest-api
     */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
    {
        // set the status
        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        header($status_header);
        // and the content type
        header('Content-type: ' . $content_type);

        // pages with body are easy
        if($body != '')
        {
            // send the body
            echo $body;
        }
        // we need to create the body if none is passed
        else
        {
            // create some body messages
            $message = '';

            // this is purely optional, but makes the pages a little nicer to read
            // for your users.  Since you won't likely send a lot of different status codes,
            // this also shouldn't be too ponderous to maintain
            switch($status)
            {
                case 401:
                    $message = 'You must be authorized to view this page.';
                    break;
                case 200:
                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                    break;
                case 500:
                    $message = 'The server encountered an error processing your request.';
                    break;
                case 501:
                    $message = 'The requested method is not implemented.';
                    break;
            }

            // servers don't always have a signature turned on 
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
                    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                    <html>
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                        <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                    </head>
                    <body>
                        <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                        <p>' . $message . '</p>
                        <hr />
                        <address>' . $signature . '</address>
                    </body>
                    </html>';

            echo $body;
        }
        //Yii::app()->end();
    }
    
    /**
     * HTTP Status Code Message
     * @param string $status
     * @return bool
     */
    private function _getStatusCodeMessage($status)
    {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            200 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
}

?>
