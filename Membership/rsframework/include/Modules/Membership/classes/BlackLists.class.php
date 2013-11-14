<?php
/**
 * BlackList Module
 * @author Mark Kenneth Esguerra
 * @date November 8, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
class BlackLists extends BaseEntity
{
    CONST ADD = 1;
    
    function BlackLists()
    {
        $this->TableName = "blacklists";
        $this->Identity = "BlackListedID";
        $this->ConnString = "membership";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    /**
     * Check if the player is already exist
     * @param string $lastname Last name of the player to be blacklisted
     * @param string $firstname First name of the player
     * @param date $birthdate Birthdate of the player
     * @param int $process Add or Update 
     * @return array Count, Status
     * @author Mark Kenneth Esguerra
     * @date November 8, 2013
     */
    public function checkIfExist($lastname, $firstname, $birthdate, $process, $blacklistID = NULL)
    {
        if ($process == 1) //A
        {
            $query = "SELECT Status, BlackListedID FROM $this->TableName 
                      WHERE LastName = '$lastname' AND FirstName = '$firstname' AND 
                      BirthDate = '$birthdate'";
            $result = parent::RunQuery($query);
        }
        else if($process == 2)
        {
            $query = "SELECT Status, BlackListedID FROM $this->TableName 
                      WHERE LastName = '$lastname' AND FirstName = '$firstname' AND 
                      BirthDate = '$birthdate' AND BlackListedID <> $blacklistID";
            $result = parent::RunQuery($query);
        }
        else if ($process == 3) //Checking in Registration
        {
            $query = "SELECT COUNT(BlackListedID) as Count FROM $this->TableName 
                      WHERE LastName = '$lastname' AND FirstName = '$firstname' AND 
                      BirthDate = '$birthdate' AND Status = 1";
            
            $result = parent::RunQuery($query);
        }
        
        return $result;
    }
    /**
     * Add player to blacklists if the player is not yet listed
     * @param string $lastname Last name of the player to be blacklisted
     * @param string $firstname First name of the player
     * @param date $birthdate Birthdate of the player
     * @param string $remarks Remarks (optional)
     * @param int $aid AID of the user
     * @return array TransCode and TransMsg
     * @author Mark Kenneth Esguerra
     * @date November 8, 2013
     */
    public function addToBlackList($lastname, $firstname, $birthdate, $remarks, $aid)
    {
        App::LoadModuleClass("Membership","AuditTrail");
        App::LoadModuleClass("Membership","AuditFunctions");
        $_Log = new AuditTrail();

        $AID = $_SESSION['userinfo']['AID'];
        $sessionID = $_SESSION['userinfo']['SessionID'];
        $this->StartTransaction();
        try
        {
            $arrInfo['LastName']        = $lastname;
            $arrInfo['FirstName']       = $firstname;
            $arrInfo['BirthDate']       = $birthdate;
            $arrInfo['DateCreated']     = "NOW_USEC()";
            $arrInfo['CreatedByAID']    = $aid;
            $arrInfo['Status']          = 1;
            
            $this->Insert($arrInfo);
            $lastblacklistID = $this->LastInsertID;
            
            if (!App::HasError())
            {
                try
                {
                    $query = "INSERT INTO blacklisthistory (BlackListedID,
                                                            CreatedByAID,
                                                            DateCreated,
                                                            Remarks,
                                                            Status
                            ) VALUES ($lastblacklistID,
                                      $aid,
                                      NOW_USEC(),
                                      '$remarks',
                                      1
                            )";
                    $result = parent::ExecuteQuery($query);
                    if ($result)
                    {
                        try
                        {
                            $this->CommitTransaction();
                            $_Log->logEvent(AuditFunctions::PLAYER_BLACKLISTING, $firstname." ".$lastname." :successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                            return array('TransCode' => 1,
                                         'TransMsg' => 'The player was successfully added in the blacklist');
                        }
                        catch (Exception $e)
                        {
                            $this->RollBackTransaction();
                            return array('TransCode' => 0,
                                         'TransMsg' => $e->getMessage());
                        }
                    }
                    else
                    {
                        $this->RollBackTransaction();
                        return array('TransCode' => 0, 
                                     'TransMsg' => 'An error occured while inserting to database');
                    }
                }
                catch (Exception $e)
                {
                    $this->RollBackTransaction();
                    return array('TransCode' => 0,
                                 'TransMsg' => $e->getMessage());
                }
            }
            else
            {
                $this->RollBackTransaction();
                return array('TransCode' => 0, 
                             'TransMsg' => 'An error occured while inserting to database');
            }
        }
        catch (Exception $e)
        {
            $this->RollBackTransaction();
            return array('TransCode' => 0,
                         'TransMsg' => $e->getMessage());
        }
    }
    /**
     * Get the details of all black listed players
     * @return array Array of black listed players
     * @author Mark Kenneth Esguerra
     * @date November 8, 2013
     */
    public function getAllBlackListed()
    {
        $query = "SELECT a.BlackListedID, a.LastName, a.FirstName, a.BirthDate, 
                         DATE_FORMAT(a.DateCreated, '%Y-%c-%d') as DateCreated 
                  FROM $this->TableName a 
                  WHERE a.Status = 1 ORDER BY a.LastName ASC";
                  
        $result = parent::RunQuery($query);
        
        return $result;
    }
    /**
     * Update the Black Listed player's details
     * @param string $lastname LastName of the blacklisted player
     * @param string $firstname FirstName of the blacklisted player
     * @param date $birthdate Birthdate
     * @param string $remarks Remarks
     * @param int $blackListedID ID of the blacklisted player
     * @return array TransCode and TransMsg
     * @author Mark Kenneth Esguerra
     * @date November 11, 2013
     */
    public function updateBlacklistedDetails($lastname, $firstname, $birthdate, $remarks, $blackListedID)
    {
        App::LoadModuleClass("Membership","AuditTrail");
        App::LoadModuleClass("Membership","AuditFunctions");
        $_Log = new AuditTrail();
        
        $AID = $_SESSION['userinfo']['AID'];
        $sessionID = $_SESSION['userinfo']['SessionID'];
        
        $this->StartTransaction();
        //Update details in blacklists table
        try
        {
            $query = "UPDATE $this->TableName SET LastName = '$lastname',
                                                  FirstName = '$firstname', 
                                                  BirthDate = '$birthdate' 
                      WHERE BlackListedID = $blackListedID AND Status = 1";
            $result = parent::ExecuteQuery($query);
            //Check if there affected rows
            $affectedrows1 = $this->AffectedRows;
            if ($result)
            {
                //Update remarks in blacklisthistory
                try
                {
                    $query = "UPDATE blacklisthistory SET Remarks = '$remarks' 
                              WHERE BlackListedID = $blackListedID AND Status = 1 
                              ORDER BY DateCreated DESC LIMIT 1";
                    $result = parent::ExecuteQuery($query);
                    //Check if there affected rows
                    $affectedrows2 = $this->AffectedRows;
                    if ($result)
                    {
                        if (($affectedrows1 || $affectedrows2) > 0)
                        {
                            try
                            {
                                $this->CommitTransaction();
                                $_Log->logEvent(AuditFunctions::UPDATE_BLACKLISTED_PLAYER, $firstname." ".$lastname." successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                                return array('TransCode' => 1,
                                             'TransMsg' => 'Blacklisted details successfully updated');
                            }
                            catch (Exception $e)
                            {
                                $this->RollBackTransaction();
                                return array('TransCode' => 0,
                                             'TransMsg' => $e->getMessage());
                            }
                        }
                        else
                        {
                            return array('TransCode' => 1,
                                         'TransMsg' => 'Record details unchanged');
                        }
                    }
                    else
                    {
                        $this->RollBackTransaction();
                        return array('TransCode' => 0, 
                                     'TransMsg' => 'An error occured while updating database');
                    }
                }
                catch (Exception $e)
                {
                    $this->RollBackTransaction();
                    return array('TransCode' => 0,
                                 'TransMsg' => $e->getMessage());
                }
            }
            else
            {
                $this->RollBackTransaction();
                return array('TransCode' => 0, 
                             'TransMsg' => 'An error occured while updating database');
            }
        }
        catch (Exception $e)
        {
            $this->RollBackTransaction();
            return array('TransCode' => 0,
                         'TransMsg' => $e->getMessage());
        }
    }
    /**
     * Set the player to blacklisted or whitelisted.
     * @param int $blacklistedID Blacklisted ID of the player to be blacklisted or whitelisted
     * @param int $aid AID of the user
     * @param int $option 1 - BlackListing, 2 - WhiteListing
     * @param string $remarks Remarks for the player to be blacklisted
     * @author Mark Kenneth Esguerra
     * @date November 11, 2013
     */
    public function changeBlackListedStat($blacklistedID, $aid, $option, $remarks = NULL)
    {
        
        App::LoadModuleClass("Membership","AuditTrail");
        App::LoadModuleClass("Membership","AuditFunctions");
        $_Log = new AuditTrail();
        
        $AID = $_SESSION['userinfo']['AID'];
        $sessionID = $_SESSION['userinfo']['SessionID'];
        
        $this->StartTransaction();
        //Blacklisting
        if ($option == 1)
        {
            //Update status in blacklists table
            try
            {
                $query = "UPDATE $this->TableName SET Status = 1 
                          WHERE BlackListedID = $blacklistedID";
                $result = parent::ExecuteQuery($query);
                if ($result)
                {
                    try
                    {
                        $query = "INSERT INTO blacklisthistory (
                                                        BlackListedID, 
                                                        CreatedByAID, 
                                                        DateCreated, 
                                                        Remarks, 
                                                        Status 
                                  ) VALUES (
                                    $blacklistedID, 
                                    $aid,
                                    NOW_USEC(), 
                                    '$remarks', 
                                    1
                        )";
                        $result = parent::ExecuteQuery($query);
                        if ($result)
                        {
                            try
                            {
                                $this->CommitTransaction();
                                $_Log->logEvent(AuditFunctions::PLAYER_BLACKLISTING, "BlackListedID ".$blacklistedID.":successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                                return array('TransCode' => 1,
                                             'TransMsg' => 'The player was successfully added in the blacklist');
                            }
                            catch (Exception $e)
                            {
                                $this->RollBackTransaction();
                                return array('TransCode' => 0,
                                             'TransMsg' => $e->getMessage());
                            }
                        }
                        else
                        {
                            $this->RollBackTransaction();
                            return array('TransCode' => 0, 
                                         'TransMsg' => 'An error occured while inserting to database');
                        }
                    }
                    catch (Exception $e)
                    {
                        $this->RollBackTransaction();
                        return array('TransCode' => 0,
                                     'TransMsg' => $e->getMessage());
                    }
                }
                else
                {
                    $this->RollBackTransaction();
                    return array('TransCode' => 0, 
                                 'TransMsg' => 'An error occured while updating the database');
                }
            }
            catch (Exception $e)
            {
                $this->RollBackTransaction();
                return array('TransCode' => 0,
                             'TransMsg' => $e->getMessage());
            }
        }
        //Whitelisting
        else if ($option == 2)
        {
            //Update status in blacklists table
            try
            {
                $query = "UPDATE $this->TableName SET Status = 0  
                          WHERE BlackListedID = $blacklistedID";
                $result = parent::ExecuteQuery($query);
                if ($result)
                {
                    try
                    {
                        $query = "INSERT INTO blacklisthistory (
                                                        BlackListedID, 
                                                        CreatedByAID, 
                                                        DateCreated, 
                                                        Remarks, 
                                                        Status
                                  ) VALUES (
                                    $blacklistedID, 
                                    $aid,
                                    NOW_USEC(), 
                                    'Whitelisted', 
                                    0
                        )";
                        $result = parent::ExecuteQuery($query);
                        if ($result)
                        {
                            try
                            {
                                $this->CommitTransaction();
                                $_Log->logEvent(AuditFunctions::REMOVE_BLACKLISTED_PLAYER, "BlackListedID ".$blacklistedID." removed:successful", array('ID'=>$AID, 'SessionID'=>$sessionID));
                                return array('TransCode' => 1,
                                             'TransMsg' => 'The player was successfully remove from the blacklist');
                            }
                            catch (Exception $e)
                            {
                                $this->RollBackTransaction();
                                return array('TransCode' => 0,
                                             'TransMsg' => $e->getMessage());
                            }
                        }
                        else
                        {
                            $this->RollBackTransaction();
                            return array('TransCode' => 0, 
                                         'TransMsg' => 'An error occured while inserting to database');
                        }
                    }
                    catch (Exception $e)
                    {
                        $this->RollBackTransaction();
                        return array('TransCode' => 0,
                                     'TransMsg' => $e->getMessage());
                    }
                }
                else
                {
                    $this->RollBackTransaction();
                    return array('TransCode' => 0, 
                                 'TransMsg' => 'An error occured while updating database');
                }
            }
            catch (Exception $e)
            {
                $this->RollBackTransaction();
                return array('TransCode' => 0,
                             'TransMsg' => $e->getMessage());
            }
        }
    }
}
?>
