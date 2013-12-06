<?php

class ManagePartnersController extends Controller {

    public $dialogmsg;
    public $dialogtitle;
    public $showdialog = false;
    public $partnerID;
   
    /**
     * Add Partner Controller
     * @author mgesguerra 
     * @date 09-20-13
     * 
     */
    public function actionAddPartner()
    {
        $model          = new ManagePartnersForm;
        $refpartner     = new RefPartnerModel();
        $validation     = new Validations();
        $partnersmodel  = new PartnersModel();
        
        if (isset($_POST['ManagePartnersForm']))
        {
            $model->attributes = $_POST['ManagePartnersForm'];
            
            $details['partnerID']       = $this->sanitize($model->PartnerID);
            $details['partnername']     = $this->sanitize($model->eGamesPartner);
            $details['address']         = $this->sanitize($model->companyAddress);
            $details['pnumber']         = $this->sanitize($model->phoneNumber);
            $details['faxnumber']       = $this->sanitize($model->faxNumber);
            $details['email']           = $this->sanitize($model->emailAddress);
            $details['website']         = $this->sanitize($model->website);
            $details['contactPerson']   = $this->sanitize($model->contactPerson);
            $details['username']        = $this->sanitize($model->username);
            $details['contactPosition'] = $this->sanitize($model->contactPosition);
            $details['contactEmail']    = $this->sanitize($model->contactEmailAddress);
            $details['contactPNumber']  = $this->sanitize($model->contactPhoneNumber);
            $details['contactMobile']   = $this->sanitize($model->contactMobile);
            $details['status']          = $this->sanitize($model->partnershipStatus);
//            $details['noOfofferings']   = $this->sanitize($model->numberOfRewardOfferings);
            $details['noOfofferings']   = 1;
            
            //Check if Partner was already exist
            $ctrpartner = $refpartner->checkPartnerIfExist($details['partnername']);
            if ($ctrpartner > 0)
            {
                $this->dialogtitle = "ERROR MESSAGE";
                $this->dialogmsg = "Partner already Exist.";
                $this->showdialog = true;
            }
            //Validate inputs
            else
            {
                //Check if username is already exist
                $ctrusername = $partnersmodel->checkUsernameExist($details['username']);
                if ($ctrusername['ctrusername'] > 0)
                {
                    $this->dialogtitle = "ERROR MESSAGE";
                    $this->dialogmsg = "Username already Exist.";
                    $this->showdialog = true;
                }
                else
                {
                    //Check if all fields are filled up
                    if ((($details['partnerID'] || $details['partnername'] || $details['address'] ||
                        $details['pnumber'] || $details['faxnumber'] || $details['email'] || 
                        $details['website'] || $details['contactPerson'] || $details['username']
                        || $details['contactPosition'] || 
                        $details['contactEmail'] || $details['contactPNumber'] 
                        || $details['contactMobile'] || $details['noOfofferings']) == "") 
                        || ($details['status'] == -1))
                    {

                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Please fill up all fields!";
                        $this->showdialog = true;
                    }
                    else if (!$validation->validateAlphaNumeric($details['partnername']) || 
                             !$validation->validateAlphaNumeric($details['address']) ||
                             !$validation->validateAlphaNumeric($details['faxnumber']) ||
                             !$validation->validateAlphaNumeric($details['username']) ||
                             !$validation->validateAlphaNumeric($details['contactPosition']) ||
                             !$validation->validateAlphaNumeric($details['contactMobile']) ||
                             !$validation->validateAlphaNumeric($details['status']) ||
                             !$validation->validateAlphaNumeric($details['noOfofferings']))
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Special characters are not allowed in some fields that \n
                                            accept only letters or numbers.";
                        $this->showdialog = true;
                    }
                    else if ($validation->validateWebsite($details['website']) == false)
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Invalid Website URL";
                        $this->showdialog = true;
                    }
                    else if (($validation->validateEmail($details['email']) == false) ||
                    $validation->validateEmail($details['contactEmail']) == false)
                    {
                       $this->dialogtitle = "ERROR MESSAGE";
                       $this->dialogmsg = "Invalid Email Address on <u>Company</u> or <u>Contact Person</u>
                                           Email Address";
                       $this->showdialog = true;
                    }

                    if ($details['noOfofferings'] <= 0)
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Number of Reward Offerings must be greater than zero.";
                        $this->showdialog = true;
                    }
                    else
                    {
                        //Validate text length
                        if ($validation->validateMinimum($details['partnername'], "PartnerName"))
                        {
                            $this->dialogtitle = "ERROR MESSAGE";
                            $this->dialogmsg = "eGames Partner Name too short (minimum is 5 characters)";
                            $this->showdialog = true;
                        }
                        else if ($validation->validateMinimum($details['address'], "CompanyName"))
                        {
                            $this->dialogtitle = "ERROR MESSAGE";
                            $this->dialogmsg = "Company Name too short (minimum is 5 characters)";
                            $this->showdialog = true;
                        }
                        else if ($validation->validateMinimum($details['pnumber'], "PhoneNumber"))
                        {
                            $this->dialogtitle = "ERROR MESSAGE";
                            $this->dialogmsg = "Phone Number too short (minimum is 5 characters)";
                            $this->showdialog = true;
                        }
                        else if ($validation->validateMinimum($details['faxnumber'], "FaxNumber"))
                        {
                            $this->dialogtitle = "ERROR MESSAGE";
                            $this->dialogmsg = "Fax Number too short (minimum is 7 characters)";
                            $this->showdialog = true;
                        }
                        else if ($validation->validateMinimum($details['contactPosition'], "ContactPosition"))
                        {
                            $this->dialogtitle = "ERROR MESSAGE";
                            $this->dialogmsg = "Contact Person's Position too short (minimum is 5 characters)";
                            $this->showdialog = true;
                        }
                        else if ($validation->validateMinimum($details['contactPNumber'], "ContactPosition"))
                        {
                            $this->dialogtitle = "ERROR MESSAGE";
                            $this->dialogmsg = "Contact Person's Phone Number too short (minimum is 7 characters)";
                            $this->showdialog = true;
                        }
                        else if ($validation->validateMinimum($details['contactMobile'], "ContactMobile"))
                        {
                            $this->dialogtitle = "ERROR MESSAGE";
                            $this->dialogmsg = "Contact Person's Mobile too short (minimum is 11 characters)";
                            $this->showdialog = true;
                        }
                        else
                        {
                            $result = $model->addPartner($details); 

                            $this->dialogmsg = $result['TransMsg'];
                            //SUCCESS or ERROR Message
                            if ($result['TransCode'] != 0)
                            {
                                $this->dialogtitle = "ERROR MESSAGE";
                            }
                            else
                            {
                                $this->dialogtitle = "SUCCESS MESSAGE";
                                //Test if php can send email in client side
                                $to = "sample@someone.com";
                                $subject = "Test";
                                $message = "Sample Message";
                                if(mail($to,$subject,$message) == false)
                                {
                                    $this->dialogtitle = "ERROR MESSAGE";
                                    $this->dialogmsg = "Email message did not send";
                                }
                                else
                                {
                                    $model->mailAddedPartner($result['Email'], $result['ContactPerson'], $result['Password'], $result['Username'], $result["ID"]);
                                }
                            }
                            $this->showdialog = true;
                        }
                    }
                }
            } 
            $this->render('index', array('model' => $model));
        }
        else
        {
            $this->redirect('index');
        }
    }

    /**
     * Lists all models.
     * Modified by: Mark Kenneth Esguerra | 09-17-13
     */
    public function actionIndex() {
        $model = new ManagePartnersForm;
        $status = "All";
        $data = $model->getPartnerDetails($status);
            
        $updateUrl = $this->createUrl('update', array('id' => ''));
        $arrayData = array();
        $ctr = 0;
        $countData = count($data);
        $arrayNewList = array();
        $arrayData = array();
        if (is_array($data) && sizeof($data) > 0) {
            if (!array_key_exists('errcode', $data)) {
                do {
                    $arrayNewList['PartnerID'] = $data[$ctr]['PartnerID'];
                    $arrayNewList['PartnerName'] = "<a href='#' id='partnerNameLink' PartnerID='".$arrayNewList['PartnerID']."'
                                                                           PartnerName='".$data[$ctr]['PartnerName']."'
                                                                           CompanyAddress='".$data[$ctr]['CompanyAddress']."'
                                                                           CompanyEmail='".$data[$ctr]['CompanyEmail']."'
                                                                           CompanyPhone='".$data[$ctr]['CompanyPhone']."'
                                                                           CompanyFax='".$data[$ctr]['CompanyFax']."'
                                                                           CompanyWebsite='".$data[$ctr]['CompanyWebsite']."'
                                                                           ContactPerson='".$data[$ctr]['ContactPerson']."'
                                                                           ContactPersonPosition='".$data[$ctr]['ContactPersonPosition']."'
                                                                           ContactPersonPhone='".$data[$ctr]['ContactPersonPhone']."'
                                                                           ContactPersonMobile='".$data[$ctr]['ContactPersonMobile']."'
                                                                           ContactPersonEmail='".$data[$ctr]['ContactPersonEmail']."'
                                                                           Status='".$data[$ctr]['Status']."'
                                                                           NumberOfRewardOffers='".$data[$ctr]['NumberOfRewardOffers']."'
                                                                           >".$data[$ctr]['PartnerName']."</a>";
                    if ($data[$ctr]['Status'] == 1) {
                        $arrayNewList['Status'] = 'Active';
                    } 
                    else if ($data[$ctr]['Status'] == 0)
                    {
                        $arrayNewList['Status'] = 'Inactive';
                    }
                    else if ($data[$ctr]['Status'] == 2)
                    {
                        $arrayNewList['Status'] = 'Deactivated';
                    }
                    $arrayNewList['NumberOfRewardOffers'] = urldecode($data[$ctr]['NumberOfRewardOffers']);
                    $arrayNewList['ContactPerson'] = urldecode($data[$ctr]['ContactPerson']);
                    $arrayNewList['ContactPersonEmail'] = urldecode($data[$ctr]['ContactPersonEmail']);
                    $arrayNewList['EditLink'] = "<div title='Edit Details'><a href='#' id='editlinkid' PartnerID='".$arrayNewList['PartnerID']."'
                                                                           PartnerName='".$data[$ctr]['PartnerName']."'
                                                                           CompanyAddress='".$data[$ctr]['CompanyAddress']."'
                                                                           CompanyEmail='".$data[$ctr]['CompanyEmail']."'
                                                                           CompanyPhone='".$data[$ctr]['CompanyPhone']."'
                                                                           CompanyFax='".$data[$ctr]['CompanyFax']."'
                                                                           CompanyWebsite='".$data[$ctr]['CompanyWebsite']."'
                                                                           ContactPerson='".$data[$ctr]['ContactPerson']."'
                                                                           ContactPersonPosition='".$data[$ctr]['ContactPersonPosition']."'
                                                                           ContactPersonPhone='".$data[$ctr]['ContactPersonPhone']."'
                                                                           ContactPersonMobile='".$data[$ctr]['ContactPersonMobile']."'
                                                                           ContactPersonEmail='".$data[$ctr]['ContactPersonEmail']."'
                                                                           Status='".$data[$ctr]['Status']."'
                                                                           NumberOfRewardOffers='".$data[$ctr]['NumberOfRewardOffers']."'
                                                                           ><img src='../../images/settings.png'></a></div>";
                    $arrayData[] = $arrayNewList;
                    $ctr = $ctr + 1;
                } while ($ctr < $countData);
            }
        }
        if (Yii::app()->request->isAjaxRequest) {
            echo jqGrid::generateJSON(10, $arrayData, 'PartnerID');
            Yii::app()->end();
        }
        unset($arrayNewList, $arrayData);
        $this->render('index', array('model' => $model));
    }

    /**
     * Performs the AJAX validation.
     * @param ManagePartners $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'manage-partners-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
    /**
     * Get Status Name
     * @param type $status StatusRef
     * @author mgesguerra
     */
    public static function determineStatus($status)
    {
        switch($status)
        {
            case 1: $stat = "Active";
                break;
            case 0: $stat = "Inactive";
                break;
            case 2: $stat = "Deactivated";
                break;
        }
        return $stat;
    }
    /**
     * Update Details Controller
     * Updates partner details when the user clicks
     * partner name
     * @author mgesguerra
     * @date Sep-18-13
     */
    public function actionUpdatedetails()
    {
        $model = new ManagePartnersForm();
        $refpartner = new RefPartnerModel();
        $validation = new Validations();
        if (isset($_POST['ManagePartnersForm']))
        {
            $model->attributes = $_POST['ManagePartnersForm'];
            
            $details['partnerID']       = $this->sanitize($model->PartnerID);
            $details['partnername']     = $this->sanitize($model->eGamesPartner);
            $details['address']         = $this->sanitize($model->companyAddress);
            $details['pnumber']         = $this->sanitize($model->phoneNumber);
            $details['faxnumber']       = $this->sanitize($model->faxNumber);
            $details['email']           = $this->sanitize($model->emailAddress);
            $details['website']         = $this->sanitize($model->website);
            $details['contactPerson']   = $this->sanitize($model->contactPerson);
            $details['contactPosition'] = $this->sanitize($model->contactPosition);
            $details['contactEmail']    = $this->sanitize($model->contactEmailAddress);
            $details['contactPNumber']  = $this->sanitize($model->contactPhoneNumber);
            $details['contactMobile']   = $this->sanitize($model->contactMobile);
            $details['status']          = $this->sanitize($model->partnershipStatus);
            $details['noOfofferings']   = $this->sanitize($model->numberOfRewardOfferings);
            
            //Error Handling (Validations)
            //Check if Partner was already exist
            $isExist =  $refpartner->checkPartnerIfExist($details['partnername'], $details['partnerID']);
            if ((int)$isExist > 0)
            {
                $this->dialogtitle = "ERROR MESSAGE";
                $this->dialogmsg = "Partner already Exist.";
                $this->showdialog = true;
            }
            else 
            {
                if ((($details['partnerID'] || $details['partnername'] || $details['address'] ||
                $details['pnumber'] || $details['faxnumber'] || $details['email'] || 
                $details['website'] || $details['contactPerson'] || $details['contactPosition'] || 
                $details['contactEmail'] || $details['contactPNumber'] 
                || $details['contactMobile']) == "" || $details['noOfofferings'] == "") || ($details['status'] == -1))
                {
                    $this->dialogtitle = "ERROR MESSAGE";
                    $this->dialogmsg = "Please fill up all fields!";
                    $this->showdialog = true;

                }
                
                if (!$validation->validateAlphaNumeric($details['partnerID']) || 
                    !$validation->validateAlphaNumeric($details['partnername']) ||
                    !$validation->validateAlphaNumeric($details['address']) ||
                    !$validation->validateAlphaNumeric($details['contactPerson']) ||
                    !$validation->validateAlphaNumeric($details['contactPosition']) ||
                    !$validation->validateAlphaNumeric($details['contactMobile']) ||
                    !$validation->validateAlphaNumeric($details['status']) ||
                    !$validation->validateAlphaNumeric($details['noOfofferings']))
                {
                    $this->dialogtitle = "ERROR MESSAGE";
                    $this->dialogmsg = "Special characters are not allowed in some fields that \n
                                        accept only letters or numbers.";
                    $this->showdialog = true;
                }
                else if ($validation->validateWebsite($details['website']) == false)
                {
                    $this->dialogtitle = "ERROR MESSAGE";
                    $this->dialogmsg = "Invalid Website URL";
                    $this->showdialog = true;
                }
                else if (($validation->validateEmail($details['email']) == false) ||
                $validation->validateEmail($details['contactEmail']) == false)
                {
                   $this->dialogtitle = "ERROR MESSAGE";
                   $this->dialogmsg = "Invalid Email Address on <u>Company</u> or <u>Contact Person</u>
                                       Email Address";
                   $this->showdialog = true;
                }

//                if ($details['noOfofferings'] <= 0)
//                {
//                    $this->dialogtitle = "ERROR MESSAGE";
//                    $this->dialogmsg = "Number of Reward Offerings must be greater than zero.";
//                    $this->showdialog = true;
//                }
                else
                {
                    //Validate text length
                    if ($validation->validateMinimum($details['partnername'], "PartnerName"))
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "eGames Partner Name too short (minimum is 5 characters)";
                        $this->showdialog = true;
                    }
                    else if ($validation->validateMinimum($details['address'], "CompanyName"))
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Company Name too short (minimum is 5 characters)";
                        $this->showdialog = true;
                    }
                    else if ($validation->validateMinimum($details['pnumber'], "PhoneNumber"))
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Phone Number too short (minimum is 5 characters)";
                        $this->showdialog = true;
                    }
                    else if ($validation->validateMinimum($details['faxnumber'], "FaxNumber"))
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Fax Number too short (minimum is 7 characters)";
                        $this->showdialog = true;
                    }
                    else if ($validation->validateMinimum($details['contactPosition'], "ContactPosition"))
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Contact Person's Position too short (minimum is 5 characters)";
                        $this->showdialog = true;
                    }
                    else if ($validation->validateMinimum($details['contactPNumber'], "ContactPosition"))
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Contact Person's Phone Number too short (minimum is 7 characters)";
                        $this->showdialog = true;
                    }
                    else if ($validation->validateMinimum($details['contactMobile'], "ContactMobile"))
                    {
                        $this->dialogtitle = "ERROR MESSAGE";
                        $this->dialogmsg = "Contact Person's Mobile too short (minimum is 11 characters)";
                        $this->showdialog = true;
                    }
                    else
                    {
                        $user = Yii::app()->session['AID'];
                        $return = $model->updatePartnerDetails($details, $user);

                        $this->dialogmsg = $return['TransMsg'];
                        //SUCCESS or ERROR Message
                        switch ($return['TransCode'])
                        {
                            case 0:
                                $this->dialogtitle = "SUCCESS MESSAGE";
                                break;
                            case 2:
                                $this->dialogtitle = "ERROR MESSAGE";
                                break;
                            case 3: 
                                $this->dialogtitle = "MESSAGE";
                                break;
                            default:
                                $this->dialogtitle = "ERROR MESSAGE";
                                break;
                        }
                        $this->showdialog = true;
                    }
                }
           }
           $this->render('index', array('model' => $model));
        }
        else
        {
            $this->redirect('index');
        }
    }
    /**
     * Sanitize inputs
     * Add here some additional function <br /> to sanitize inputss
     * @param type $str String to be sanitize
     * @return $str Sanitized input
     * @author mgesguerra
     */
    private function sanitize($str)
    {
        $str = trim($str);
        
        return mysql_escape_string($str);
    }
    
    /**
     * Filter Partner Views using AJAX. View Partners by Active, 
     * Inactive or All then load to JqGrid
     * @author mgesguerra
     * @date Sept. 27, 2013
     */
    public function actionAjaxViewPartnersBy()
    {
        $model = new ManagePartnersForm();
        $page = $_POST['page'];
        $limit = $_POST['rows'];

        $status = $_POST['Status'];
        
        $data = $model->getPartnerDetails($status);
        
        $count = count($data);
        $response = array();
        if ($count > 0)
        {
            $total_pages = ceil($count/$limit);
        }
        else
        {   
            $total_pages = 0;
        }
        if ($page > $total_pages)
        {
            $page = $total_pages;
        }

        $start = $limit * $page - $limit;
        if($count == 0)
            $start = 0;
        
        $response["page"] = $page;
        $response["total"] = $total_pages;
        $response["records"] = $count;
        //Check there are fetched data
        if ($count > 0)
        {
            $i = 0; 
            foreach ($data as $val)
            {
                if ($val['Status'] == 1)
                {
                    $val['StatusName'] = 'Active';
                } 
                else if ($val['Status'] == 0)
                {
                    $val['StatusName'] = 'Inactive';
                }
                else if ($val['Status'] == 2)
                {
                    $val['StatusName'] = 'Deactivated';
                }
                    
                $response["rows"][$i]['id'] = $val['PartnerID'];
                $response["rows"][$i]['cell'] = array("<a href='#' id='partnerNameLink' PartnerID='".$val['PartnerID']."'
                                                                           PartnerName='".$val['PartnerName']."'
                                                                           CompanyAddress='".$val['CompanyAddress']."'
                                                                           CompanyEmail='".$val['CompanyEmail']."'
                                                                           CompanyPhone='".$val['CompanyPhone']."'
                                                                           CompanyFax='".$val['CompanyFax']."'
                                                                           CompanyWebsite='".$val['CompanyWebsite']."'
                                                                           ContactPerson='".$val['ContactPerson']."'
                                                                           ContactPersonPosition='".$val['ContactPersonPosition']."'
                                                                           ContactPersonPhone='".$val['ContactPersonPhone']."'
                                                                           ContactPersonMobile='".$val['ContactPersonMobile']."'
                                                                           ContactPersonEmail='".$val['ContactPersonEmail']."'
                                                                           Status='".$val['Status']."'
                                                                           StatusName='".$val['StatusName']."'
                                                                           NumberOfRewardOffers='".$val['NumberOfRewardOffers']."'
                                                                           >".$val['PartnerName']."</a>",
                                                    $val['StatusName'],
                                                    $val['NumberOfRewardOffers'],
                                                    $val['ContactPerson'],
                                                    $val['ContactPersonEmail'],
                                                    "<div title='Edit Details'><a href='#' id='editlinkid' PartnerID='".$val['PartnerID']."'
                                                                           PartnerName='".$val['PartnerName']."'
                                                                           CompanyAddress='".$val['CompanyAddress']."'
                                                                           CompanyEmail='".$val['CompanyEmail']."'
                                                                           CompanyPhone='".$val['CompanyPhone']."'
                                                                           CompanyFax='".$val['CompanyFax']."'
                                                                           CompanyWebsite='".$val['CompanyWebsite']."'
                                                                           ContactPerson='".$val['ContactPerson']."'
                                                                           ContactPersonPosition='".$val['ContactPersonPosition']."'
                                                                           ContactPersonPhone='".$val['ContactPersonPhone']."'
                                                                           ContactPersonMobile='".$val['ContactPersonMobile']."'
                                                                           ContactPersonEmail='".$val['ContactPersonEmail']."'
                                                                           Status='".$val['Status']."'
                                                                           NumberOfRewardOffers='".$val['NumberOfRewardOffers']."'
                                                                           ><img src='../../images/settings.png'></a></div>"
                                                    );
                $i++;
            }
        }
        else
        {
            $i = 0;
            $response["page"] = $page;
            $response["total"] = $total_pages;
            $response["records"] = $count;
            $msg = "Audit Trail: No returned result";
            $response["msg"] = $msg;
        }
        echo json_encode($response);
    }
    public function actionAutoLogout() {
        
        $page = $_POST['page'];
 
        if($page =='logout'){

            echo json_encode('logouts');
            //Force Logout even without clicking OK
            $aid = Yii::app()->session['AID'];
            $sessionmodel = new SessionForm();
            $sessionmodel->deleteSession($aid);
            Yii::app()->user->logout();
        } 
    }
}
