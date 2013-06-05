<?php

/**
 * @author owliber
 * @date Oct 2, 2012
 * @filename VoucherAPIController.php
 * 
 */

class VoucherAPIController extends Controller
{
    CONST SOURCE_KAPI = 1;
    CONST SOURCE_EGM = 2;
    CONST SOURCE_CASHIER = 3;
    
    CONST CASH_TYPE_BILL = 3;
    CONST VOUCHER_USED_STATUS = 3;
    
    /**
     * Method to validate every API call to confirm if source
     * is legitimate by validating the passed token code from KAPI
     * @param string $token
     * @return boolean
     */
    public function validateToken($token)
    {
        //Validate token first if valid
        $validatetoken = Yii::app()->params->validateTokenURL .'?token='.$token;
        $valresult = CJSON::decode(Yii::app()->CURL->run($validatetoken));

        if($valresult['ValidateToken']['ErrorCode'] > 0)
            return false;
        else
            return true;
    }
    
    public function actionVerify()
    {   
        /**
         * This is the verify method called during the Withdrawal process.
         * Once API did not return any result, the requesting system should
         * call verify using the same unique trackingID used during the 
         * withdrawal. This will check if the trackingID was successfully 
         * inserted on the system or not.
         */
        if(isset($_GET['trackingid']) && ctype_alnum($_GET['trackingid'])
            && isset($_GET['aid']) && is_numeric($_GET['aid'])
            && isset($_GET['source']) && is_numeric($_GET['source']))
        {   
            $trackingid = $_GET['trackingid'];
            $AID = $_GET['aid'];
            $source = $_GET['source'];
            
            if($source == self::SOURCE_EGM)
            {
                if(isset($_GET['token']) && ctype_alnum($_GET['token']))
                {
                    $token = $_GET['token'];
                    
                    //Check if token is valid
                    if($this->validateToken($token)) //true
                    {
                        $model = new VoucherAPI();
                        $result = $model->verifyTrackingID($AID,$trackingid,$source);

                        $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>$result)));
                    }
                    else
                    {
                        //Log to audit trail
                        $details = 'Invalid token '.$token;
                        AuditLog::logAPITransactions(1, $source, $details, $token, $trackingid, 2);

                        $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>array("VoucherCode"=>"",
                                                                                             "VoucherTypeID"=>"",
                                                                                             "Amount"=>floatval(0),
                                                                                             "DateCreated"=>"",
                                                                                             "TransMsg"=>"Invalid Token",
                                                                                             "ErrorCode"=>intval(10)
                                                                                       )
                                                                     )
                        ));
                    }
                }
                else
                {
                    
                    
                    //Log to audit trail
                    $details = 'Token required';
                    AuditLog::logAPITransactions(1, $source, $details, $AID, $trackingid, 2);
                    
                    $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>array("VoucherCode"=>"",
                                                                                             "VoucherTypeID"=>"",
                                                                                             "Amount"=>floatval(0),
                                                                                             "DateCreated"=>"",
                                                                                             "TransMsg"=>"Token required",
                                                                                             "ErrorCode"=>intval(11)
                                                                                       )
                                                                     )
                        ));
                }
                
            }
            else
            {
                $model = new VoucherAPI();
                $result = $model->verifyTrackingID($AID,$trackingid,$source);

                $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>$result)));
            }
            
            
        }
        /**
         * This is another verify method called during the DEPOSIT or RELOAD process.
         * API will check the Ticket/Voucher/Coupon status if still valid.
         */
        elseif(isset($_GET['vouchercode']) && is_numeric($_GET['vouchercode'])
            && isset($_GET['aid']) && is_numeric($_GET['aid'])
            && isset($_GET['source']) && is_numeric($_GET['source']))
            //&& isset($_GET['trackingid']) && ctype_alnum($_GET['trackingid']))
        {
            $vouchercode = $_GET['vouchercode'];
            $AID = $_GET['aid'];
            $source = $_GET['source'];
            
            $model = new VoucherAPI();
                        
            $info = $model->getVoucherInfo($vouchercode);
            $trackingid = $info['TrackingID'];
            
            if($source == self::SOURCE_EGM)
            {
                if(isset($_GET['token']) && ctype_alnum($_GET['token']))
                {
                    $token = $_GET['token'];
                    
                    //Check if token is valid
                    if($this->validateToken($token)) //true
                    {
                        //Log to audit trail
                        $model = new VoucherAPI();
                        $result = $model->verifyVoucher($AID,$vouchercode,$source,$trackingid);

                        $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>$result)));
                    }
                    else
                    {
                        //Log to audit trail
                        $details = 'Invalid token '.$token;
                        AuditLog::logAPITransactions(1, $source, $details, $token, $trackingid, 2);

                        $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>array("VoucherCode"=>"",
                                                                                             "VoucherTypeID"=>"",
                                                                                             "Amount"=>floatval(0),
                                                                                             "DateCreated"=>"",
                                                                                             "LoyaltyCreditable"=>"",
                                                                                             "TransMsg"=>"Invalid Token",
                                                                                             "ErrorCode"=>intval(10)
                                                                                       )
                                                                     )
                        ));
                    }
                    
                }
                else
                {
                   //Log to audit trail
                    $details = 'Token required ';
                    AuditLog::logAPITransactions(1, $source, $details, $AID, $trackingid, 2);

                    $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>array("VoucherCode"=>"",
                                                                                         "VoucherTypeID"=>"",
                                                                                         "Amount"=>floatval(0),
                                                                                         "DateCreated"=>"",
                                                                                         "LoyaltyCreditable"=>"",
                                                                                         "TransMsg"=>"Token required",
                                                                                         "ErrorCode"=>intval(11)
                                                                                   )
                                                                 )
                    )); 
                }
                
            }
            else
            {
                //Log to audit trail
                $details = 'Verifying voucher '.$vouchercode;
                AuditLog::logAPITransactions(1, $source, $details, $vouchercode, $trackingid, 1);
                    
                $model = new VoucherAPI();
                $result = $model->verifyVoucher($AID,$vouchercode,$source,$trackingid);

                $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>$result)));
            }
            
            
        }
        else
        {
            //Log to audit trail
            $token = isset($_GET['token']) ? $_GET['token'] : NULL;
            $trackingid = isset($_GET['trackingid']) ? $_GET['trackingid'] : NULL;
            
            $source = isset($_GET['source']) ? $_GET['source'] : NULL;
            
            $details = 'Parameter error';
            
            AuditLog::logAPITransactions(1, $source, $details, $token, $trackingid, 2);
                
            $this->_sendResponse(200, CJSON::encode(array("VerifyVoucher"=>array("VoucherCode"=>"",
                                                                                    "VoucherTypeID"=>"",
                                                                                    "Amount"=>floatval(0),
                                                                                    "DateCreated"=>"",
                                                                                    "TransMsg"=>"Voucher is invalid",
                                                                                    "ErrorCode"=>intval(12)
                                                                                )
                                                        )
                    ));
        }
        
        
    }
    
    /**
     * Use ACTIVE or VOID tickets
     * VOID tickets can be accepted depends on
     * the parameters if set to TRUE of FALSE
     */
    public function actionUse()
    {
        if(isset($_GET["vouchercode"]) && is_numeric($_GET["vouchercode"])
            && isset($_GET["aid"]) && is_numeric($_GET["aid"])
            && isset($_GET['trackingid']) && ctype_alnum($_GET['trackingid'])
            && isset($_GET['terminalid']) && is_numeric($_GET['terminalid'])
            && isset($_GET['source']) && is_numeric($_GET['source']))
        {
            $voucherCode = trim($_GET["vouchercode"]);
            $AID = trim($_GET["aid"]);
            $trackingID = trim($_GET['trackingid']);
            $terminalID = trim($_GET['terminalid']);
            $source = $_GET['source'];
            
            if($source == self::SOURCE_EGM)
            {
                //Require EGM to input token
                if(isset($_GET['token']) && ctype_alnum($_GET['token']))
                {
                    $token = $_GET['token'];
                    
                    if($this->validateToken($token))
                    {
                        $model = new VoucherAPI();
                        $result = $model->useVoucher($terminalID,$AID,$voucherCode,$trackingID,$source);

                        $this->_sendResponse(200, CJSON::encode(array("UseVoucher"=>$result)));
                    }
                    else
                    {
                        //Log to audit trail
                        $details = 'Invalid token '.$token;
                        AuditLog::logAPITransactions(2, $source, $details, $token, $trackingID, 2);

                        $this->_sendResponse(200, CJSON::encode(array("UseVoucher"=>array("TransMsg"=>"Invalid Token",
                                                                                           "ErrorCode"=>intval(11)
                                                                                        )
                                                                     )
                                ));
                    }
                }
                else
                {
                    //Log to audit trail
                    $details = 'Token required';
                    AuditLog::logAPITransactions(2, $source, $details, $terminalID, $trackingID, 2);
                    
                    $this->_sendResponse(200, CJSON::encode(array("UseVoucher"=>array("TransMsg"=>"Token required",
                                                                                           "ErrorCode"=>intval(12)
                                                                                        )
                                                                     )
                                ));
                }
                
                
            }
            else
            {
                $model = new VoucherAPI();
                $result = $model->useVoucher($terminalID,$AID,$voucherCode,$trackingID,$source);

                $this->_sendResponse(200, CJSON::encode(array("UseVoucher"=>$result)));
            }
            
        }
        else
        {
            //Log to audit trail
            $trackingID = isset($_GET['trackingid']) ? $_GET['trackingid'] : null;
            $token = isset($_GET['token']) ? $_GET['token'] : null;
            
            $details = 'Parameter error';
            AuditLog::logAPITransactions(2, $source, $details, $token, $trackingID, 2);
            
            $this->_sendResponse(200, CJSON::encode(array("UseVoucher"=>array("TransMsg"=>"Parameter error",
                                                                                "ErrorCode"=>intval(13)
                                                                            )
                                                            )
                    ));
        }
       
        
    }
    
    /**
     * Generate ticket
     */
    public function actionGenerate()
    {
        
        if(isset($_GET["amount"]) && is_numeric($_GET["amount"])
            && isset($_GET["terminalid"]) && is_numeric($_GET["terminalid"])
            && isset($_GET['aid'])        && is_numeric($_GET['aid'])
            && isset($_GET["trackingid"]) && ctype_alnum($_GET["trackingid"])
            && isset($_GET['source'])   && is_numeric($_GET['source'])
            && isset($_GET['token']) && ctype_alnum($_GET['token']))
        {
            
            $terminalID = trim($_GET["terminalid"]); //Machine number
            $trackingID = trim($_GET["trackingid"]); //Unique identifier
            $amount = trim($_GET["amount"]); //Must be > 0
            $AID = trim($_GET['aid']); //Dummy account ID
            $source = trim($_GET['source']); // 
            $token = $_GET['token'];
            
            //Validate token if source is from EGM machine, otherwise bypass if source is from KAPI
            if($source == self::SOURCE_EGM)
            {
                if($this->validateToken($token))
                {
                    if($amount > 0)
                    {
                        $model = new VoucherAPI();
                        $result = $model->generateVoucher($trackingID, $terminalID, $AID, $amount, $source);

                        $this->_sendResponse(200, CJSON::encode($result));
                    }
                    else
                    {
                        //Log to audit trail
                        $details = 'Invalid amount '.$amount;
                        AuditLog::logAPITransactions(3, $source, $details, $terminalID, $trackingID, 2);
                    
                        $this->_sendResponse(200, CJSON::encode(array("GenerateVoucher"=>array("VoucherCode"=>"",
                                                                                               "TerminalID"=>"",
                                                                                               "Amount"=>floatval(0),
                                                                                               "DateCreated"=>"",
                                                                                               "DateExpiry"=>"",
                                                                                               "TransMsg"=>"Invalid Amount",
                                                                                               "ErrorCode"=>intval(4)
                                                                                            )
                                                                    )
                                ));
                    }
                }
                else
                {
                    //Log to audit trail
                    $details = 'Invalid token '.$token;
                    AuditLog::logAPITransactions(3, $source, $details, $terminalID, $trackingID, 2);
                    
                    $this->_sendResponse(200, CJSON::encode(array("GenerateVoucher"=>array("VoucherCode"=>"",
                                                                                               "TerminalID"=>"",
                                                                                               "Amount"=>floatval(0),
                                                                                               "DateCreated"=>"",
                                                                                               "DateExpiry"=>"",
                                                                                               "TransMsg"=>"Invalid Token",
                                                                                               "ErrorCode"=>intval(5)
                                                                                        )
                                                                )
                            ));
                }
            }
            else
            {
                if($amount > 0)
                {
                    $model = new VoucherAPI();
                    $result = $model->generateVoucher($trackingID, $terminalID, $AID, $amount, $source);

                    $this->_sendResponse(200, CJSON::encode($result));
                }
                else
                {
                    //Log to audit trail
                    $details = 'Invalid amount '.$amount;
                    AuditLog::logAPITransactions(3, $source, $details, $terminalID, $trackingID, 2);
            
                    $this->_sendResponse(200, CJSON::encode(array("GenerateVoucher"=>array("VoucherCode"=>"",
                                                                                           "TerminalID"=>"",
                                                                                           "Amount"=>floatval(0),
                                                                                           "DateCreated"=>"",
                                                                                           "DateExpiry"=>"",
                                                                                           "TransMsg"=>"Invalid Amount",
                                                                                           "ErrorCode"=>intval(4)
                                                                                        )
                                                                    )
                            ));
                }
            }
            
            
        
            
        }
        else
        {
            //Log to audit trail
            $details = 'Parameter error';
            AuditLog::logAPITransactions(3, $source, $details, $terminalID, $trackingID, 2);
             
            $this->_sendResponse(200, CJSON::encode(array("GenerateVoucher"=>array("VoucherCode"=>"",
                                                                                   "TerminalID"=>"",
                                                                                   "Amount"=>floatval(0),
                                                                                   "DateCreated"=>"",
                                                                                   "DateExpiry"=>"",
                                                                                   "TransMsg"=>"Parameter error",
                                                                                   "ErrorCode"=>intval(6)
                                                                                )
                                                        )
                    ));
        }
       
        
    }
    
    /**
     * Log stacker cash-ins
     */
    public function actionLogStacker()
    {
        
        if(isset($_GET['cashtype']) && is_numeric($_GET['cashtype'])
                && isset($_GET['amount']) && is_numeric($_GET['amount'])
                && isset($_GET['transtype']) && is_numeric($_GET['transtype'])
                && isset($_GET['terminalid']) && is_numeric($_GET['terminalid'])
                && isset($_GET['trackingid']) && ctype_alnum($_GET['trackingid'])
                && isset($_GET['token']) && ctype_alnum($_GET['token']))
        {
            // 1 - Ticket; 2 - Coupon; 3 - Cash
            $cashType = $_GET['cashtype'];
            $token = $_GET['token'];
            
            if($this->validateToken($token))
            {
                $amount = $_GET['amount'];

                // 1 - Deposit; 2 - Reload  
                $transType = $_GET['transtype'];
                $terminalID = $_GET['terminalid'];

                $trackingid = $_GET['trackingid'];
                
                if($cashType < self::CASH_TYPE_BILL)
                {
                    if(isset($_GET['vouchercode']) && is_numeric($_GET['vouchercode']))
                    {
                        $voucherCode = $_GET['vouchercode'];
                    }
                    else
                    {
                        //Log to audit trail
                        $details = 'Parameter error';                        
                        AuditLog::logAPITransactions(4, self::SOURCE_EGM, $details, $terminalID, $trackingid, 2);
            
                        $this->_sendResponse(200, CJSON::encode(array("LogStacker"=>array("TransMsg"=>"Parameter error1",
                                                                                          "ErrorCode"=>intval(4)
                                                                                        )
                                                                     )
                                ));
                    }

                }
                else
                {
                    $voucherCode = null;
                }

                $model = new VoucherAPI();

                $result = $model->logStackerCashIn($cashType, $amount, $terminalID, $transType, $trackingid, $voucherCode);

                $this->_sendResponse(200, CJSON::encode($result));
            }
            else
            {
                //Log to audit trail
                $details = 'Invalid token '.$token;                        
                AuditLog::logAPITransactions(4, self::SOURCE_EGM, $details, $terminalID, $trackingid, 2);
            
                $this->_sendResponse(200, CJSON::encode(array("LogStacker"=>array("TransMsg"=>"Invalid Token",
                                                                                  "ErrorCode"=>intval(3)
                                                                                 )
                                                            )
                        ));
            }
            
            
        }
        else
        {
            //Log to audit trail
            $details = 'Parameter error';                       
            AuditLog::logAPITransactions(4, self::SOURCE_EGM, $details, $terminalID, $trackingid, 2);
                
            $this->_sendResponse(200, CJSON::encode(array("LogStacker"=>array("TransMsg"=>"Parameter error2",
                                                                              "ErrorCode"=>intval(4)
                                                                            )
                                                        )
                    ));
        }
    }
    
    public function actionVerifyStacker()
    {
        if(isset($_GET['trackingid']) && ctype_alnum($_GET['trackingid']))
        {
            $trackingid = $_GET['trackingid'];
            
            //Log to audit trail
            $details = 'Verify stacker';
            AuditLog::logAPITransactions(5, self::SOURCE_EGM, $details, null, $trackingid, 1);
                    
            $model = new VoucherAPI();
            
            $result = $model->verifyStackerCashIn($trackingid);
            
            $this->_sendResponse(200, CJSON::encode($result));
        }
        else
        {
            //Log to audit trail
            $trackingid = isset($_GET['trackingid']) ? $_GET['trackingid'] : null;
            
            $details = 'Parameter error';
            AuditLog::logAPITransactions(5, self::SOURCE_EGM, $details, null, $trackingid, 2);
                        
            $this->_sendResponse(200, CJSON::encode(array("VerifyStacker"=>array("TransMsg"=>"Parameter error",
                                                                                 "ErrorCode"=>intval(2)
                                                                                )
                                                          )
                    ));
        }
    }
    
    public function actionStackerSession()
    {
        if(isset($_GET['machineid']) && isset($_GET['date']) && isset($_GET['action']))
        {
            $machineid = $_GET['machineid'];
            $date = $_GET['date'];
            //1 - Insert Stacker; 2 - Remove Stacker
            $action = $_GET['action']; 
            
            $model = new VoucherAPI();
            
            $machine_id = $model->getMachineID($machineid);
                                    
            if(!empty($machine_id))            
                $result = $model->logStackerSession($machine_id,$date,$action);
            else
            {
                $details = 'Invalid machineid '.$machineid;
                AuditLog::logAPITransactions(6, self::SOURCE_EGM, $details, $machineid, null, 2);
                
                $result = array("StackerSession"=>array("TransMsg"=>"Invalid machine id.",
                                                 "ErrorCode"=>intval(3)
                            ));
            }
                
            $this->_sendResponse(200, CJSON::encode($result));
            
        }
        else
        {
            $machineid = isset($_GET['machineid']) ? $_GET['machineid'] : null;
            
            //Log to audit trail
            $details = 'Parameter error';
            AuditLog::logAPITransactions(6, self::SOURCE_EGM, $details, $machineid, null, 2);
            
            $this->_sendResponse(200, CJSON::encode(array("StackerSession"=>array("TransMsg"=>"Parameter error",
                                                                                  "ErrorCode"=>intval(2)
                                                                                )
                                                        )
                    ));
        }
    }
       
    /**
     * 
     * @param int $status
     * @param string $body
     * @param string $content_type
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
                case 404:
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
        Yii::app()->end();
    }
    
    /**
     * 
     * @param int $status
     * @return string
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
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
    
}
?>
