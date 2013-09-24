<?php

class ManagePartnersForm extends CFormModel
{
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
            array('eGamesPartner', 'required'),
            array('eGamesPartner', 'length', 'max' => 30, 'min' => 5),
            array('companyAddress', 'required'),
            array('companyAddress', 'length', 'max' => 20, 'min' => 5),
            array('companyName', 'required'),
            array('companyName', 'length', 'max' => 30, 'min' => 5),
            array('phoneNumber', 'required'),
            array('phoneNumber', 'length', 'max' => 20, 'min' => 5),
            array('faxNumber', 'required'),
            array('faxNumber', 'length', 'max' => 20, 'min' => 5),
            array('emailAddress', 'required'),
            array('eGamesParemailAddress', 'length', 'max' => 30, 'min' => 5),
            array('website', 'required'),
            array('website', 'length', 'max' => 30, 'min' => 5),
            array('contactPerson', 'required'),
            array('contactPerson', 'length', 'max' => 30, 'min' => 5),
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
            array('numberOfRewardOfferings', 'length', 'max' => 20, 'min' => 5),
        );
    }
    
    public function getPartnerDetails()
    {
        $query = "SELECT rp.PartnerID, rp.PartnerName, rp.Status,
                         pd.CompanyAddress, pd.CompanyEmail, pd.CompanyPhone,
                         pd.CompanyFax, pd.CompanyWebsite, pd.ContactPerson,
                         pd.ContactPersonPosition, pd.ContactPersonPhone,
                         pd.ContactPersonMobile, pd.ContactPersonEmail, pd.NumberOfRewardOffers
                         FROM partnerdetails pd
                         INNER JOIN ref_partners rp ON rp.PartnerID = pd.PartnerID WHERE rp.Status IN (0, 1);";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->queryAll();
    }
    
    public function addPartner()
    {
        $query = "INSERT INTO ref_partners(PartnerName, DateCreated, CreatedByAID, Status)
                         VALUES('$this->eGamesPartner', NOW(), '', '$this->partnershipStatus')";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->execute();
    }
    
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

        foreach ($result as $PartnerName) {
            $this->zeGamesPartner = $PartnerName['PartnerName'];
        }
        var_dump($this->zeGamesPartner); exit;
    }
    
    
}

?>