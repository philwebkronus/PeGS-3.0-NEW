<?php
/* * ***************** 
 * Author: Renz Tiratira
 * Date Created: 2013-04-17
 * ***************** */
class LoyaltyPointsTransfer extends BaseEntity
{
    function LoyaltyPointsTransfer()
    {
        $this->TableName = "cardpointstransfer";
        $this->ConnString = "loyalty";
        $this->Identity = "CardPointsTransferID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    function GetOldCard($oldcardnumber)
    {
        $query = "select OldCardID, CardNumber, Username, CardTypeID, LifetimePoints, (LifetimePoints - RedeemedPoints) as CurrentPoints, RedeemedPoints, CardStatus from oldcards where CardNumber='".$oldcardnumber."'";
        
        $result = parent::RunQuery($query);
        return $result;
    }
    
    function GetNewCard($newcardnumber)
    {
        $query = "select MemberCardID, MID, MemberCardName, CardNumber, LifetimePoints, CurrentPoints, RedeemedPoints, BonusPoints, RedeemedBonusPoints, Status 
        from membercards 
        where CardNumber='".$newcardnumber."'
        -- and Status = 1";
        
        $result = parent::RunQuery($query);
        return $result;
    }
    
    function ProcessCardPointsTransfer($arrCardPointsTransfer, $arrCardPoints, $arrOldCards)
    {
        $this->StartTransaction();
        try
        {
            $this->Insert($arrCardPointsTransfer);
            if(!App::HasError())
            {
                App::LoadModuleClass("Loyalty", "MemberCards");
                $_CardPoints = new MemberCards();
                $_CardPoints->PDODB = $this->PDODB;
                
                $arrCardPoints["MemberCardID"] = $arrCardPointsTransfer["ToMemberCardID"];
                $_CardPoints->UpdateByArray($arrCardPoints);
                /*$membercardid = $arrCardPointsTransfer["ToMemberCardID"];
                $lifetimepoints = $arrCardPoints["LifeTimePoints"];
                $currentpoints = $arrCardPoints["CurrentPoints"];
                $redeemedpoints = $arrCardPoints["RedeemedPoints"];
                
                $_CardPoints->UpdateCardPoints($membercardid, $lifetimepoints, $currentpoints, $redeemedpoints);*/
                if(!App::HasError())
                {
                    App::LoadModuleClass("Loyalty", "OldCards");
                    $_OldCards = new OldCards();
                    $_OldCards->PDODB = $this->PDODB;
                    
                    $arrOldCards["OldCardID"] = $arrCardPointsTransfer["FromOldCardID"];
                    $_OldCards->UpdateByArray($arrOldCards);
                    //$oldcardid = $arrCardPointsTransfer["FromOldCardID"];
                    //$cardstatus = $arrOldCards["CardStatus"];
                    
                    //$_OldCards->UpdateCardStatus($oldcardid, $cardstatus);
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
            else
            {
                $this->RollBackTransaction();
            }
        }
        catch (Exception $e)
        {
            $this->RollBackTransaction();
            App::SetErrorMessage($e->getMessage());
        }
    }
}

?>
