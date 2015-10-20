<?php
/* * ***************** 
 * Author: Renz Tiratira
 * Date Created: 2013-04-22
 * ***************** */
class LoyaltyPointsTransferFromOld extends BaseEntity
{
    function LoyaltyPointsTransferFromOld()
    {
        $this->TableName = "membercards";
        $this->ConnString = "loyalty";
        $this->Identity = "MemberCardID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    function ProcessCardPointsTransferOld($arrMemberCards, $arrCardPointsTransfer, $arrCards, $arrOldCards)
    {
        $this->StartTransaction();
        try
        {
            $this->Insert($arrMemberCards);
            if(!App::HasError())
            {
                App::LoadModuleClass("Loyalty", "CardPointsTransfer");
                $_CardPointsTransfer = new CardPointsTransfer();
                $_CardPointsTransfer->PDODB = $this->PDODB;

                $arrCardPointsTransfer['ToMemberCardID'] = $this->LastInsertID;
                $_CardPointsTransfer->Insert($arrCardPointsTransfer);
                
                if(!App::HasError())
                {
                    App::LoadModuleClass("Loyalty", "Cards");
                    $_Cards = new Cards();
                    $_Cards->PDODB = $this->PDODB;

                    $arrCards["CardID"] = $arrMemberCards["CardID"];
                    $_Cards->UpdateByArray($arrCards);
                    if(!App::HasError())
                    {
                        App::LoadModuleClass("Loyalty", "OldCards");
                        $_OldCards = new OldCards();
                        $_OldCards->PDODB = $this->PDODB;

                        $arrOldCards["OldCardID"] = $arrCardPointsTransfer["FromOldCardID"];
                        $_OldCards->UpdateByArray($arrOldCards);
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

