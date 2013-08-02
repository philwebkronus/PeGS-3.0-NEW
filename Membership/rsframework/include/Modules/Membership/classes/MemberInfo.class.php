<?php

/*
 * @author : owliber
 * @date : 2013-04-18
 */

class MemberInfo extends BaseEntity
{
    
    public function MemberInfo()
    {
        $this->ConnString = 'membership';
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "memberinfo";
        $this->Identity = "MID";
    }
    
    /**
     * 
     * @param int $MID - Member ID
     * @return string array of member details
     */
    public function getMemberInfo( $MID )
    {
        
        $query = "SELECT
                    m.*,
                    mi.*
                  FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.MID = $MID";
        
        return parent::RunQuery($query);
        
    }
    
    public function getEmail($MID){
        $query = "SELECT Email FROM $this->TableName WHERE MID=$MID";
        $result = parent::RunQuery($query);
        return $result[0]["Email"];
    }

        public function getMIDFNameUsingEmail( $email )
    {
        
        $query = "SELECT MID, FirstName, LastName FROM ".$this->TableName."
                  WHERE Email = '$email' ";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getEmailFNameUsingMID( $MID )
    {
        
        $query = "SELECT Email, FirstName, LastName, Status FROM ".$this->TableName."
                  WHERE MID = $MID ";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getMemberInfoByUsername( $Username )
    {
        
        $query = "SELECT
                    m.Password,
                    mi.*
                  FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.Username = '$Username'";
        
        return parent::RunQuery($query);
        
    }
    
    /*
     * Description: Get Member Info using name that match either the firstname, middlename or the lastname.
     * @author: aqdepliyan
     * DateCreated: 2013-06-17 05:40:20PM
     */
    public function getMemberInfoByName( $name )
    {
        
        $query = "SELECT mi.MID, mi.FirstName, mi.LastName , mi.Birthdate, mi.IdentificationNumber, ri.IdentificationName
                            FROM memberinfo as mi
                            INNER JOIN ref_identifications as ri ON ri.IdentificationID = mi.IdentificationID
                            WHERE mi.FirstName like '%".$name."%' OR mi.LastName like '%".$name."%' OR
                            CONCAT(mi.FirstName, ' ', mi.LastName) LIKE  '%".$name."%'";
        return parent::RunQuery($query);
    }
    
    /*
     * Description: Get Member Info using MID with a status limit only to active, active temporary and banned cards..
     * @author: aqdepliyan
     * DateCreated: 2013-06-17 05:40:20PM
     */
    public function getMemberInfoByMID( $MID )
    {
        
        $query = "SELECT m.Status, mi.FirstName, mi.LastName, mi.Birthdate, mi.IdentificationNumber, ri. IdentificationName
                            FROM members as m
                            INNER JOIN memberinfo as mi ON mi.MID = m.MID
                            INNER JOIN ref_identifications as ri ON ri.IdentificationID = mi.IdentificationID
                            WHERE m.MID =".$MID;
        
        return parent::RunQuery($query);
    }
    
    
    /*
     * Description: Get the number of Active Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 11:40:05AM
     */
    public function getActiveAccountStatus()
    {
        
        $query = "SELECT COUNT(MID), Status FROM members WHERE Status = 1";
        
        return parent::RunQuery($query);
    }
    
    /*
     * Description: Get the number of Banned Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 11:41:43AM
     */
    public function getBannedAccountStatus()
    {
        
        $query = "SELECT COUNT(MID), Status FROM members WHERE Status = 5";
        
        return parent::RunQuery($query);
    }
    
    public function getDateVerifiedByMID($MID)
    {
        $query = "SELECT DateVerified FROM $this->TableName WHERE MID = $MID;";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /*
     * Description: Get the number of both Active and Banned Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 01:07:35PM
     */
    public function getActiveAndBannedAccountStatus()
    {
        
        $query = "SELECT COUNT(MID) FROM members WHERE Status IN (1, 5)";
        
        return parent::RunQuery($query);
    }
    
    /*
     * Description: Get the number of both Active and Banned Account Status
     * @author: Junjun S. Hernandez
     * DateCreated: June 27, 2013 01:07:35PM
     */
    
        public function getMemberInfoByID($MID)
    {
        $query = "SELECT YEAR(current_date)-YEAR(mi.Birthdate) as Age, mi.MID, mi.Gender, m.Status FROM memberinfo mi
                    INNER JOIN members m ON mi.MID = m.MID
                  WHERE m.MID = $MID";
        return parent::RunQuery($query);
    }
    
    public function updateProfile( $arrMembers, $arrMemberInfo)
    {
        
        $this->StartTransaction();
        
        try 
        { 
            if(!empty($arrMembers) || $arrMembers != ''){
                $this->TableName = "members";
                $this->Identity = "MID";
                $updatedprofile = $this->UpdateByArray($arrMembers); 
            }
            else{
                $updatedprofile = 0;
            }
                   
        
            //App::Pr($arrMembers);
             
            if(!App::HasError())
            {
                $this->TableName = "memberinfo";
                $this->Identity = "MID";
                $updatedinfo = $this->UpdateByArray($arrMemberInfo);

                if($updatedinfo == 1){
                    $arrMemberInfo['DateUpdated'] = 'now_usec()';
                    if(isset($_SESSION['MID']))
                    $arrMemberInfo['UpdatedByAID'] = $_SESSION['MID'];
                    if(isset($_SESSION['aID']))
                    $this->UpdateByArray($arrMemberInfo);
                }
                //App::Pr($arrMemberInfo);
                
                if(!App::HasError())     
                {
                    //App::Pr($this);
                    $this->CommitTransaction();
                    if($updatedprofile == 1 && $updatedinfo == 0 || $updatedprofile == 0 && $updatedinfo == 1
                            || $updatedprofile == 1 && $updatedinfo == 1){
                        $message = "You have successfully updated your profile.";
                        return $message;
                    } else {
                        $message ="Account details unchanged.";
                        return $message;
                    }
                    //App::SetSuccessMessage('Update Profile Successful');
                    
                } 
            }
            else
            {
                //echo App::GetErrorMessage();
                $this->RollBackTransaction();
                $message = "Update profile failed.";
                return $message;
            }
        }
        catch (Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }
    
    public function updateProfileAdmin( $arrMembers, $arrMemberInfo)
    {
        
        $this->StartTransaction();
        
        try 
        {          
            $this->TableName = "members";
            $this->Identity = "MID";
            $this->UpdateByArray($arrMembers);        
        
            //App::Pr($arrMembers);
             
            if(!App::HasError())
            {
                $this->TableName = "memberinfo";
                $this->Identity = "MID";
                $arrMemberInfo["MID"] = $arrMembers["MID"];
                $this->UpdateByArray($arrMemberInfo);

                //App::Pr($arrMemberInfo);
                
                if(!App::HasError())     
                {
                    //App::Pr($this);
                    $this->CommitTransaction();
                    if($this->AffectedRows == 0){
                        $message = "Profile Details Unchanged.";
                        return $message;
                    } else {
                        $message =" You have successfully updated your profile.";
                        return $message;
                    }
                    //App::SetSuccessMessage('Update Profile Successful');
                    
                } 
            }
            else
            {
                //echo App::GetErrorMessage();
                $this->RollBackTransaction();
                $message = "Update profile failed.";
                return $message;
            }
        }
        catch (Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }
    
    function updateProfileForCouponAjax($arrEntries)
    {
        unset($_SESSION["PreviousRemdeption"]);
        $this->Identity = "MemberInfoID";
        parse_str($arrEntries, $entries);
        if (isset($entries["TermsAndConditions"]))
        {
            unset($entries["TermsAndConditions"]);
        }

        foreach ($entries as $key => $val)
        {
            $entries[$key] = urldecode($val);
        }
        //$query = "Update tbl_Players set Name='$name', BirthDate='$birthdate', Address='$address', CityID='$city', RegionID='$region', EmailAdd='$email', ContactNumber='$contactno' where ID = $playerid;";
        parent::UpdateByArray($entries);
        //$retval = $this->LastQuery;
        if ($this->HasError)
        {
            $retval =  $this->getError();
        }
        else
        {
            $retval =  "Profile Updated Successfully.";
        }
//        App::LoadCore("File.class.php");
//        $filename = dirname(__FILE__) . "/posts.txt";
//        $fp = new File($filename);
//        $fp->WriteAppend("Last Query: " . $this->LastQuery . "\r\n");
        return $retval;
    }
    
        function updateProfileWithNoEmail($arrEntries){
            unset($_SESSION["PreviousRedemption"]);
            $this->Identity = "MemberInfoID";
            parse_str($arrEntries, $entries);
            if (isset($entries["TermsAndConditions"])) {
                unset($entries["TermsAndConditions"]);
            }
            foreach ($entries as $key => $val){
                $entries[$key] = urldecode($val);
            }
            if(isset($_SESSION["CardRed"])){
                $entries["MemberInfoID"] = $_SESSION["CardRed"]["MemberInfoID"];
            }
            parent::UpdateByArray($entries);
            if ($this->HasError){
                $retval =  $this->getError();
            } else {
                $retval =  "Profile Updated Successfully.";
            }
            return $retval;
        
    }
    
   
    
    /**
      * @author Gerardo V. Jagolino Jr.
      * @return object array
      * get member information MID and Birthdate 
     */
    public function getBirthdays($gender, $fromdate, $todate)
    {
        
        $query = "SELECT mi.MemberInfoID, mi.MID, mi.Birthdate FROM memberinfo mi INNER JOIN members m ON m.MID = mi.MID 
            WHERE mi.Gender = $gender
                AND mi.DateVerified >= '$fromdate' AND mi.DateVerified <= '$todate' AND m.Status IN (1,5);";
        
        return parent::RunQuery($query);
        
    }
    
}
?>
