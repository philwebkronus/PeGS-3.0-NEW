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
    
    public function updateProfile( $arrMembers, $arrMemberInfo)
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
                    //App::SetSuccessMessage('Update Profile Successful');
                    
                } 
            }
            else
            {
                //echo App::GetErrorMessage();
                $this->RollBackTransaction();
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
    
    
}
?>
