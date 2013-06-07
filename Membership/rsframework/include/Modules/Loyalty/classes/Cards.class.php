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
        
        if(strpos($cardnumber, '000') !== false)
        {
            return CardVersion::OLD;
            
        }
        
        if(strpos($cardnumber, 'eGames') !== false)
        {
            return CardVersion::TEMPORARY;
        }
        
        if(strpos($cardnumber, 'UB') !== false)
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
            $tempcardid = $arrTempCard['CardID'];
            
            $this->UpdateByArray($arrTempCard);
            $this->ExecuteQuery("UPDATE membercards SET Status = $tempstatus WHERE CardID = $tempcardid");
            
            if(!App::HasError())
            {
                $this->UpdateByArray($arrNewCard);
                
                if(!App::HasError())
                {
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
        catch(Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }

}?>
