<?php

class ManagePartnersForm extends CFormModel
{
    public $username;
    public $eGamesPartner;
    public $companyAddress;
    public $companyName;   
    public $phoneNumber;
    public $faxNumber;
    public $emailAddress;
    public $website;
    public $contactPerson;
    public $contactPosition;   
    public $contactPhoneNumber;
    public $contactMobile;
    public $contactEmailAddress;
    public $partnershipStatus;
    public $numberOfRewardOfferings;
    public $PartnerID;
    public $presentStatus;
    
    public $zeGamesPartner;
    public $zcompanyAddress;
    public $zcompanyName;   
    public $zphoneNumber;
    public $zfaxNumber;
    public $zemailAddress;
    public $zwebsite;
    public $zcontactPerson;
    public $zcontactPosition;   
    public $zcontactPhoneNumber;
    public $zcontactMobile;
    public $zcontactEmailAddress;
    public $zpartnershipStatus;
    public $znumberOfRewardOfferings;
    
    public function rules() {
        return array(
            array('PartnerID, presentStatus', 'required'),
            array('eGamesPartner', 'required'),
            array('eGamesPartner', 'length', 'max' => 30, 'min' => 5),
            array('companyAddress', 'required'),
            array('companyAddress', 'length', 'max' => 150, 'min' => 5),
            array('companyName', 'required'),
            array('companyName', 'length', 'max' => 30, 'min' => 5),
            array('phoneNumber', 'required'),
            array('phoneNumber', 'length', 'max' => 20, 'min' => 5),
            array('faxNumber', 'required'),
            array('faxNumber', 'length', 'max' => 20, 'min' => 5),
            array('emailAddress', 'required'),
            array('emailAddress', 'length', 'max' => 30, 'min' => 5),
            array('website', 'required'),
            array('website', 'length', 'max' => 30, 'min' => 5),
            array('contactPerson', 'required'),
            array('contactPerson', 'length', 'max' => 30, 'min' => 5),
            array('username', 'required'),
            array('username', 'length', 'max' => 20, 'min' => 5),
            array('contactPosition', 'required'),
            array('contactPosition', 'length', 'max' => 30, 'min' => 5),
            array('contactPhoneNumber', 'required'),
            array('contactPhoneNumber', 'length', 'max' => 20, 'min' => 5),
            array('contactMobile', 'required'),
            array('contactMobile', 'length', 'max' => 20, 'min' => 5),
            array('contactEmailAddress', 'required'),
            array('contactEmailAddress', 'length', 'max' => 30, 'min' => 5),
            array('partnershipStatus', 'required'),
            array('partnershipStatus', 'length', 'max' => 20, 'min' => 5),
            array('numberOfRewardOfferings', 'required'),
            array('numberOfRewardOfferings', 'length', 'max' => 9, 'min' => 5),
        );
    }
    //Added by: mgesguerra - 09-18-13
    //To change Partner's Details Labels 
    public function attributeLabels()
    {
        return array(
            'eGamesPartner' => 'eGames Partner',
            'companyAddress' => 'Company Name',
            'contactPerson' => 'Contact Person',
            'contactPhoneNumber' => 'Phone Number',
            'contactEmailAddress' => 'Email Address',
            'contactPosition' => 'Position',
            'contactMobile' => 'Mobile'
        );
    }
    public function getPartnerDetails($status)
    {
        if ($status == "All")
        {
            $query = "SELECT rp.PartnerID, rp.PartnerName, rp.Status,
                             pd.CompanyAddress, pd.CompanyEmail, pd.CompanyPhone,
                             pd.CompanyFax, pd.CompanyWebsite, pd.ContactPerson,
                             pd.ContactPersonPosition, pd.ContactPersonPhone,
                             pd.ContactPersonMobile, pd.ContactPersonEmail, pd.NumberOfRewardOffers
                             FROM partnerdetails pd
                             INNER JOIN ref_partners rp ON rp.PartnerID = pd.PartnerID WHERE rp.Status IN (0, 1, 2);";
        
        }
        else
        {
            $query = "SELECT rp.PartnerID, rp.PartnerName, rp.Status,
                             pd.CompanyAddress, pd.CompanyEmail, pd.CompanyPhone,
                             pd.CompanyFax, pd.CompanyWebsite, pd.ContactPerson,
                             pd.ContactPersonPosition, pd.ContactPersonPhone,
                             pd.ContactPersonMobile, pd.ContactPersonEmail, pd.NumberOfRewardOffers
                             FROM partnerdetails pd
                             INNER JOIN ref_partners rp ON rp.PartnerID = pd.PartnerID WHERE rp.Status = :status";
        }
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":status", $status);
        
        return $sql->queryAll();
    }
//    public function addPartner()
//    {
//        $query = "INSERT INTO ref_partners(PartnerName, DateCreated, CreatedByAID, Status)
//                         VALUES('$this->eGamesPartner', NOW(), '', '$this->partnershipStatus')";
//        $sql = Yii::app()->db->createCommand($query);
//        return $sql->execute();
//    }
    
    public function addPartnerDetails()
    {
        $lastinsertedid = Yii::app()->db->getLastInsertID();
        $query = "INSERT INTO partnerdetails(PartnerID, CompanyEmail, CompanyPhone, CompanyFax, CompanyWebsite, ContactPerson,
                         ContactPersonPosition, ContactPersonPhone, ContactPersonMobile, ContactPersonEmail, NumberOfRewardOffers)
                         VALUES($lastinsertedid,'$this->emailAddress', '$this->phoneNumber','$this->faxNumber', '$this->website', '$this->contactPerson', '$this->contactPosition', '$this->contactPhoneNumber', '$this->contactMobile','$this->contactEmailAddress', '$this->numberOfRewardOfferings');";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->execute();
    }
    
    public function getPartnerDetailsByID($id)
    {
        $query = "SELECT rp.PartnerID, rp.PartnerName, rp.Status,
                         pd.CompanyAddress, pd.CompanyEmail, pd.CompanyPhone,
                         pd.CompanyFax, pd.CompanyWebsite, pd.ContactPerson,
                         pd.ContactPersonPosition, pd.ContactPersonPhone,
                         pd.ContactPersonMobile, pd.ContactPersonEmail, pd.NumberOfRewardOffers
                         FROM partnerdetails pd
                         INNER JOIN ref_partners rp ON rp.PartnerID = pd.PartnerID
                         WHERE rp.PartnerID = $id AND rp.Status IN (0, 1);";
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
        
        return $result;
    }
    /**
     * Update Partner Details
     * @param array $details Array of details
     * @author Mark Kenneth Esguerra
     */
    public function updatePartnerDetails($details, $user)
    {
        $audittrailmodel    = new AuditTrailModel();
        
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        //Get partner's details
        $partnerID          = $details['partnerID'];
        $partnername        = $details['partnername'];
        $address            = $details['address'];
        $pnumber            = $details['pnumber'];
        $faxnumber          = $details['faxnumber'];
        $email              = $details['email'];
        $website            = $details['website'];
        $contactPerson      = $details['contactPerson'];
        $contactPosition    = $details['contactPosition'];
        $contactEmail       = $details['contactEmail'];
        $contactPNumber     = $details['contactPNumber'];
        $contactMobile      = $details['contactMobile'];
        $status             = $details['status'];
        $numberofofferings  = $details['noOfofferings'];
        
        try
        {
            //Update the tbl_ref_partners
            $firstquery = "UPDATE ref_partners SET 
                                PartnerName = :partnername,
                                Status = :status,
                                DateUpdated = NOW_USEC(),
                                UpdatedByAID = :AID
                           WHERE PartnerID = :partnerID
                          ";

            $sql = $connection->createCommand($firstquery);
            $sql->bindParam(":partnername", $partnername);
            $sql->bindParam(":partnerID", $partnerID);
            $sql->bindParam(":status", $status);
            $sql->bindParam(":AID", $user);
            $firstresult = $sql->execute();

            if ($firstresult >= 0)
            {
                try
                {               
                    //Update tbl_partnerdetails
                    $secondquery = "UPDATE partnerdetails SET 
                                        CompanyAddress = :address,
                                        CompanyEmail = :email,
                                        CompanyPhone = :phonenumber,
                                        CompanyFax = :faxnumber,
                                        CompanyWebsite = :website,
                                        ContactPerson = :contactperson,
                                        ContactPersonPosition = :contactposition,
                                        ContactPersonPhone = :contactphone,
                                        ContactPersonMobile = :contactmobile,
                                        ContactPersonEmail = :contactemail,
                                        NumberOfRewardOffers = :numberofofferings
                                    WHERE PartnerID = :partnerID
                                    ";
                    $sql = $connection->createCommand($secondquery);
                    $sql->bindParam(":partnerID", $partnerID);
                    $sql->bindParam(":address", $address);
                    $sql->bindParam(":email", $email);
                    $sql->bindParam(":phonenumber", $pnumber);
                    $sql->bindParam(":faxnumber", $faxnumber);
                    $sql->bindParam(":website", $website);
                    $sql->bindParam(":contactperson", $contactPerson);
                    $sql->bindParam(":contactposition", $contactPosition);
                    $sql->bindParam(":contactphone", $contactPNumber);
                    $sql->bindParam(":contactmobile", $contactMobile);
                    $sql->bindParam(":contactemail", $contactEmail);
                    $sql->bindParam(":numberofofferings", $numberofofferings);
                    $secondresult = $sql->execute(); 
                    //Check whether first or second query has been updated
                    if ($secondresult > 0 || $firstresult > 0)
                    {
                        try
                        {
                            //Update tbl_partnerinfo
                            $secondquery = "UPDATE partnersinfo a 
                                            INNER JOIN partners b ON a.PartnerPID = b.PartnerPID 
                                            SET 
                                                a.Name = :contactperson,
                                                a.Address = :address,
                                                a.Email = :contactemail,
                                                a.Landline = :contactphone,
                                                a.MobileNumber = :contactmobile,
                                                a.Designation = :contactposition 
                                            WHERE b.RefPartnerID = :partnerID
                                            ";
                            $sql = $connection->createCommand($secondquery);
                            $sql->bindParam(":partnerID", $partnerID);
                            $sql->bindParam(":address", $address);
                            $sql->bindParam(":contactperson", $contactPerson);
                            $sql->bindParam(":contactposition", $contactPosition);
                            $sql->bindParam(":contactphone", $contactPNumber);
                            $sql->bindParam(":contactmobile", $contactMobile);
                            $sql->bindParam(":contactemail", $contactEmail);
                            
                            $updateresult = $sql->execute();
                            
                            if ($updateresult > 0 || $secondresult > 0 || $firstresult > 0)
                            {
                                //Check the selected status, if ACTIVE to INACTIVE, change the status
                                //of the partner's corresponding item, if INACTIVE to ACTIVE, do not 
                                //change the status
                                if ($status == 0)
                                {
                                
                                    //If partner's status has been change, change also the status of 
                                    //its corresponding item
                                    $thirdquery = "UPDATE rewarditems SET Status = :status
                                                   WHERE PartnerID = :partnerID";
                                    $sql = $connection->createCommand($thirdquery);
                                    $sql->bindParam(":partnerID", $partnerID);
                                    $sql->bindParam(":status", $this->determineItemStat($status));
                                    $thirdresult = $sql->execute();
                                }
                                else
                                {
                                    $thirdresult = 0;
                                }
                                if ($thirdresult > 0 || $secondresult > 0 || $firstresult > 0)
                                {
                                    try
                                    {
                                        $pdo->commit();
                                        //Log to Audit Trail
                                        $getstatus = $this->getStat($status);
                                        $audittrailmodel->logEvent(RefAuditFunctionsModel::MARKETING_EDIT_PARTNER_DETAILS,"PartnerID: ".$partnerID." Name: ".$partnername." Status: ".$getstatus, array('SessionID' => Yii::app()->session['SessionID'], 
                                                                                                                             'AID' => Yii::app()->session['AID']));
                                        return array('TransMsg'=>'Successfully Updated Partner Details',
                                                     'TransCode'=>0);
                                    }
                                    catch (CDbException $e)
                                    {
                                        $pdo->rollback();
                                        return array('TransMsg'=>'Error to update Partner Details',
                                                     'TransCode'=>2);
                                    }
                                }
                            }
                        }
                        catch (CDbException $e)
                        {
                            $pdo->rollback();
                            return array('TransMsg'=>'Error: Failed to update transactional table [0004]',
                                 'TransCode'=>2);
                        }
                    }
                    else
                    {
                        return array('TransMsg'=>'Record details unchanged.',
                             'TransCode'=>3);
                    }


                }
                catch (CDbException $e)
                {
                    $pdo->rollback();

                    return array('TransMsg'=>'Error: Failed to update transactional table [0002]',
                                 'TransCode'=>2);
                }
            }
        }
        catch (CDbException $e)
        {
            $pdo->rollback();

            return array('TransMsg'=>'Error: Failed to update transactional table [0001]',
                         'TransCode'=>2);
        }
    }
    /**
     * Add Partner (ref_partners and partnerdetails)
     * @param array $details Array of data inputs
     * @return array TransMsg and TransCode
     * @author Mark Kenneth Esguerra
     * @date 09-20-13
     */
    public function addPartner($details)
    {
        $audittrailmodel = new AuditTrailModel();
        
        $connection = Yii::app()->db;
        
        $pdo = $connection->beginTransaction();
        //Get partner's details
        $partnername        = $details['partnername'];
        $address            = $details['address'];
        $pnumber            = $details['pnumber'];
        $faxnumber          = $details['faxnumber'];
        $email              = $details['email'];
        $website            = $details['website'];
        $contactPerson      = $details['contactPerson'];
        $username           = $details['username'];
        $contactPosition    = $details['contactPosition'];
        $contactEmail       = $details['contactEmail'];
        $contactPNumber     = $details['contactPNumber'];
        $contactMobile      = $details['contactMobile'];
        $status             = $details['status'];
        $numberofofferings  = $details['noOfofferings'];
        $aid                = Yii::app()->session['AID'];
        //Update the tbl_ref_partners
        $firstquery = "INSERT INTO ref_partners (PartnerName, DateCreated, CreatedByAID, Status) 
                       VALUES (:partnername, NOW(), :aid, :status)";
        $sql = $connection->createCommand($firstquery);
        $sql->bindParam(":partnername", $partnername);
        $sql->bindParam(":aid", $aid);
        $sql->bindParam(":status", $status);
        $firstresult = $sql->execute();
        //Get Last Insert PID
        $lastInsertPID = $connection->getLastInsertID();
        if ($firstresult > 0)
        {
            //Check if the first execution is successful, else rollback
            try
            {               
                //Update tbl_partnerdetails
                $secondquery = "INSERT INTO partnerdetails (PartnerID, 
                                                            CompanyAddress,
                                                            CompanyEmail,
                                                            CompanyPhone,
                                                            CompanyFax,
                                                            CompanyWebsite,
                                                            ContactPerson,
                                                            ContactPersonPosition,
                                                            ContactPersonPhone,
                                                            ContactPersonMobile,
                                                            ContactPersonEmail)
                               VALUES (:partnerID,
                                       :address,
                                       :email,
                                       :phonenumber,
                                       :faxnumber,
                                       :website,
                                       :contactperson,
                                       :contactposition,
                                       :contactphone,
                                       :contactmobile,
                                       :contactemail
                                       )";
                $sql = $connection->createCommand($secondquery);
                $sql->bindParam(":address", $address);
                $sql->bindParam(":email", $email);
                $sql->bindParam(":phonenumber", $pnumber);
                $sql->bindParam(":faxnumber", $faxnumber);
                $sql->bindParam(":website", $website);
                $sql->bindParam(":contactperson", $contactPerson);
                $sql->bindParam(":contactposition", $contactPosition);
                $sql->bindParam(":contactphone", $contactPNumber);
                $sql->bindParam(":contactmobile", $contactMobile);
                $sql->bindParam(":contactemail", $contactEmail);
                $sql->bindParam(":partnerID", $lastInsertPID);
                $secondresult = $sql->execute();
                //Check if all details are successfully inserted in ref_partners and
                //partnerdetails.
                if ($secondresult > 0)
                {
                    $password = sha1("temppass". date('Y-m-d H:i:s'));
                    //Insert data into partners table. Get contact person's details
                    try
                    {
                        $thirdquery = "INSERT INTO partners (UserName, 
                                                             Password,
                                                             RefPartnerID,
                                                             AccountTypeID,
                                                             LoginAttempts,
                                                             ForChangePassword,
                                                             DateCreated,
                                                             CreatedByAID,
                                                             Status)
                                        VALUES(
                                            :username,
                                            :password,
                                            :refpartnerID,
                                            14,
                                            0,
                                            0,
                                            NOW(),
                                            :createdAID,
                                            :status
                                         )";
                        $sql = $connection->createCommand($thirdquery);
                        $sql->bindParam(":username", $username);
                        $sql->bindParam(":password", $password);
                        $sql->bindParam(":refpartnerID", $lastInsertPID);
                        $sql->bindParam(":status", $status);
                        $sql->bindParam(":createdAID", $aid);
                        $thirdresult = $sql->execute();
                        //Check if details successfully inserted in partners table
                        //If successfully, insert other details in partnersinfo table
                        $lastInsertPartnerPID = $connection->getLastInsertID();
                        if ($thirdresult > 0)
                        {
                            try
                            {
                                $fourthquery = "INSERT INTO partnersinfo (PartnerPID,
                                                             Name, 
                                                             Address,
                                                             Email,
                                                             Landline,
                                                             MobileNumber,
                                                             Designation)
                                        VALUES(
                                            :partnerPID,
                                            :name,
                                            :address,
                                            :email,
                                            :landline,
                                            :mobile,
                                            :designation)";
                                $sql = $connection->createCommand($fourthquery);
                                $sql->bindParam(":partnerPID", $lastInsertPartnerPID);
                                $sql->bindParam(":name", $contactPerson);
                                $sql->bindParam(":address", $address);
                                $sql->bindParam(":email", $contactEmail);
                                $sql->bindParam(":landline", $contactPNumber);
                                $sql->bindParam(":mobile", $contactMobile);
                                $sql->bindParam(":designation", $contactPosition);
                                $fourthresult = $sql->execute();
                                
                                if ($fourthresult > 0)
                                {
                                    try
                                    {
                                        $pdo->commit();
                                        //Log to Audit Trail
                                        $status = $this->getStat($status);
                                        $audittrailmodel->logEvent(RefAuditFunctionsModel::MARKETING_ADD_PARTNER, "PartnerID: ".$lastInsertPID." Name: ".$partnername." Status: ".$status, array('SessionID' => Yii::app()->session['SessionID'], 
                                                                                                                                      'AID' => Yii::app()->session['AID']));
                                        return array('TransMsg'=>'Partner successfully added.',
                                                     'TransCode'=>0, 'Email' => $contactEmail,
                                                     'Username' => $username,
                                                     'ContactPerson' => $contactPerson, 'Password' => $password,
                                                     'ID' => $lastInsertPartnerPID
                                                    );
                                        return array('TransMsg'=>'Partner successfully added.',
                                                     'TransCode'=>0, 'Email' => $contactEmail,
                                                     'Username' => $username,
                                                     'ContactPerson' => $contactPerson, 'Password' => $password,
                                                     'ID' => $lastInsertPartnerPID
                                                    );
                                    }
                                    catch(CDbException $e)
                                    {
                                        $pdo->rollback();
                                        return array('TransMsg'=>'Error: Failed to insert in transactional table [0004]',
                                                     'TransCode'=>2);
                                    }
                                }
                                else
                                {
                                    $pdo->rollback();
                                    return array('TransMsg'=>'Error: Failed to insert in transactional table [0004]',
                                                 'TransCode'=>2);
                                }
                            }
                            catch(CDbException $e)
                            {
                                $pdo->rollback();
                                return array('TransMsg'=>'Error: Failed to insert in transactional table [0004]',
                                             'TransCode'=>2);
                            }
                        }
                        else
                        {
                            $pdo->rollback();
                            return array('TransMsg'=>'Error: Failed to insert in transactional table [0003]',
                                         'TransCode'=>2);
                        }
                    }
                    catch (CDbException $e)
                    {
                        $pdo->rollback();
                        return array('TransMsg'=>'Error: Failed to insert in transactional table [0003]',
                                     'TransCode'=>2);
                    }
                }
                else
                {
                        $pdo->rollback();
                        return array('TransMsg'=>'Error: Failed to insert in transactional table [0002]',
                                     'TransCode'=>2);
                }                
            }
            catch (CDbException $e)
            {
                $pdo->rollback();
                return array('TransMsg'=>'Error: Failed to insert in transactional table [0002]',
                             'TransCode'=>2);
            }
        }
        else
        {
            $pdo->rollback();
            return array('TransMsg'=>'Error: Failed to insert in transactional table [0001]',
            'TransCode'=>2);
        }
    }
    /**
     * View Partners by Status. If <i>Active</i>, display <br />
     * all active partners, if <i>Inactive</i>, display all <br />
     * all inactive partners.
     * @param int $filter 1 - Active | 2 - Inactive
     */
    public function viewPartnersBy($stat)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT rp.PartnerID, rp.PartnerName, rp.Status,
                         pd.CompanyAddress, pd.CompanyEmail, pd.CompanyPhone,
                         pd.CompanyFax, pd.CompanyWebsite, pd.ContactPerson,
                         pd.ContactPersonPosition, pd.ContactPersonPhone,
                         pd.ContactPersonMobile, pd.ContactPersonEmail, pd.NumberOfRewardOffers
                         FROM partnerdetails pd
                         INNER JOIN ref_partners rp ON rp.PartnerID = pd.PartnerID WHERE rp.Status = '$stat';";
        $command = $connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Send email for newly added partner's contact person
     * @param string $to Email Address of the contact person
     * @param string $contactperson Contact person's name
     * @param string $password Temp Password
     * @author Mark Kenneth Esguerra
     * @date September 27, 2013
     * @return boolean
     */
    public function mailAddedPartner($to, $contactperson, $password, $username, $senttoaid)
    {
        $autoemaillogs = new AutoEmailLogsModel();
        $AID = Yii::app()->session['AID'];
        $SentToCCAID = null;
        $SentToBCCAID = null;
        
        $subroot = "";
        if (isset(Yii::app()->params['subrootfolder']))
        {
            $subroot = "/".Yii::app()->params['subrootfolder'];
        }
        $servername = $_SERVER['HTTP_HOST'].$subroot;
        
        $subject  = "Change Initial Password";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .=  "From: "."RewardsItemManagement@philweb.com.ph"."\r\n";
        
        $detail = "
                <html>
                <head>
                  <title>Rewards Item Management</title>
                </head>
                <body>
                  <i>Hi Mr/Ms. $contactperson</i> <br /><br />
                  <p>It is advisable that you change your password upon log-in. </p>

                  <p>Please click through the link provided below to log-in to your account. </p>
                  
                  <a href='http://$servername/index.php/updatePassword/index?username=$username&password=$password'><b>Change Initial Password</b></a>
                </body>
                </html>
                ";
        
        mail($to, $subject, $detail, $headers);
        //Log to Audit trail
        $autoemaillogs->InsertAutoEmailLogs(RefAutoEmailTypeModel::ADD_PARTNER, $senttoaid, $SentToCCAID, $SentToBCCAID, $detail, $AID);
    }
    /**
     * Change the Reference Status as RewardItem and Ref_partners have <br />
     * different reference status scheme
     * @param int $stat ref status of the partner
     * @author Mark Kenenth Esguerra
     */
    private function determineItemStat($stat)
    {
        switch ($stat)
        {
            //Partner-----Item
            case 0: $stat = 2;
                break;
            case 1: $stat = 1;
                break;
            case 2: $stat = 4;
                break;
        }
        return $stat;
    }
    /**
     * Get Status
     * @param int $stat 
     * @return string Status
     * 
     */
    public function getStat($stat)
    {
        if ($stat == 1)
        {
            $status = "Active";
        }
        else if ($stat == 0)
        {
            $status = "Inactive";
        }
        
        return $status;
    }
}

?>