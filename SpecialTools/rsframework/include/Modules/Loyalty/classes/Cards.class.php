<?php

/*
 * @author : owliber
 * @date : 2013-04-18
 */

class Cards extends BaseEntity
{   
    
    public function Cards() 
    {
        $this->TableName = "cards";
        $this->ConnString = "loyalty";
        $this->Identity = "CardID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    public function getVersion( $cardnumber )
    {
        App::LoadModuleClass("Loyalty", "CardVersion");
        
        $cardnumber = trim($cardnumber);
        
        if(strpos(substr($cardnumber, 0,3), '000') !== false)
        {
            return CardVersion::OLD;
            
        }
        
        if(strpos(substr($cardnumber, 0,6), 'eGames') !== false)
        {
            return CardVersion::TEMPORARY;
        }
        
        if((strpos(substr($cardnumber, 0,2), 'UB') !== false ) || (strpos(substr($cardnumber, 0,2), 'ub') !== false) )
        {
            return CardVersion::USERBASED;
        }
        
    }
        
    public function isExist( $cardnumber )
    {
        $query = "SELECT * FROM cards WHERE CardNumber = '$cardnumber'";
        $result = parent::RunQuery($query);
        
        if(count($result) > 0)
            return true;
        else
            return false;
    }
    
    public function getCardInfo( $cardnumber )
    {
        $query = "SELECT * FROM cards WHERE CardNumber = '$cardnumber'";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function generateCard( $cardnumber, $AID )
    {
        App::LoadModuleClass("Loyalty", "CardTypes");
        $_CardTypes = new CardTypes();
        
        $this->StartTransaction();        
        
        $arrEntries['CardNumber'] = $cardnumber;
        $arrEntries['CardTypeID'] = $_CardTypes->getCardTypeByName('Temporary');
        $arrEntries['DateCreated'] = 'now_usec()';
        $arrEntries['CreatedByAID'] = $AID;
        $arrEntries['Status'] = CardStatus::ACTIVE;
        
        $this->Insert($arrEntries);
        
        try
        {
            if(!App::HasError())
                $this->CommitTransaction ();
            else
                $this->RollBackTransaction ();
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }
    
    public function updateCardStatus($arrNewCard, $arrTempCard)
    {
        
        $this->StartTransaction();
        try
        {
            $tempstatus = $arrTempCard['Status'];
            $tempsiteid = $arrTempCard['SiteID'];
            $tempcardid = $arrTempCard['CardID'];
            
            //$this->UpdateByArray($arrTempCard);
            $this->ExecuteQuery("UPDATE membercards SET Status = $tempstatus, SiteID = $tempsiteid WHERE CardID = $tempcardid");
            
            if(!App::HasError())
            {
                $this->UpdateByArray($arrNewCard);
                
                if(!App::HasError())
                {
                    $memcardid = $arrNewCard['CardID'];
                    $this->ExecuteQuery("UPDATE loyaltydb.cards SET CardTypeID = 2 WHERE CardID = $memcardid");

                    if (!App::HasError()) {
                        $this->CommitTransaction();
                    }
                    else
                    {
                        $this->RollBackTransaction();
                    }
                }
                else
                {
                    $this->RollBackTransaction();
                }
            }
            else
            {
                $this->RollBackTransaction();
            }
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }
    
    public function updateCardsStatus2($cardid1, $cardid2, $status1, $status2, $aid, $dateupdated)
    {
        
        $this->StartTransaction();
        try
        {
            $this->ExecuteQuery("UPDATE loyaltydb.cards SET Status = $status1, UpdatedByAID = $aid, 
                DateUpdated = '$dateupdated' WHERE CardID = $cardid1");
            
            if(!App::HasError())
            { 
                if(!App::HasError())
                {
                    $this->ExecuteQuery("UPDATE loyaltydb.cards SET Status = $status2, UpdatedByAID = $aid, 
                DateUpdated = '$dateupdated' WHERE CardID = $cardid2");

                    if (!App::HasError()) {
                        $this->CommitTransaction();
                    }
                    else
                    {
                        $this->RollBackTransaction();
                    }
                }
                else
                {
                    $this->RollBackTransaction();
                }
            }
            else
            {
                $this->RollBackTransaction();
            }
        }
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }

}?>
