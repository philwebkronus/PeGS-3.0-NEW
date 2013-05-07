<?php

/*
 * @author : owliber
 * @date : 2013-04-26
 */

class ProcessPointsAPI extends BaseEntity
{
    public function ProcessPointsAPI()
    {
        $this->ConnString = "loyalty";
        $this->TableName = "cardtransactions";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    public function AddPoints( $cardnumber, $transactionid, $transdate, $paymenttype, $transactiontype, $amount, $siteid, $serviceid, $terminallogin, $vouchercode="", $iscreditable=0)
    {
        
        if(is_array($this->GetCardInfo( $cardnumber )))
        {
            
            $cardInfo = $this->GetCardInfo( $cardnumber );
            
            $arrEntries['MID'] = $cardInfo['MID'];
            $arrEntries['CardID'] = $cardInfo['CardID'];
            
            $arrEntries['TransactionID'] = $transactionid;
            $arrEntries['TransactionDate'] = $transdate;
            $arrEntries['PaymentType'] = $paymenttype;
            $arrEntries['TransactionType'] = $transactiontype;
            $arrEntries['TerminalLogin'] = $terminallogin;
            
            $iscreditable == 1 ? $arrEntries['VoucherCode'] = $vouchercode : "";            
            $arrEntries['IsCreditable'] = $iscreditable;            
            
            $arrEntries['Amount'] = $amount;
            $arrEntries['SiteID'] = $siteid;
            $arrEntries['ServiceID'] = $serviceid;
            
            //Points conversion = ;
            $conversion = $this->GetPointsConversion(); 
            $PointValue = $conversion[0]['Value'];
            $EquivalentPoint = $conversion[0]['EquivalentPoints'];
            
            $Points = ( $amount / $PointValue) * $EquivalentPoint;
            
            $arrEntries['Points'] = $Points;
            
            $arrEntries['DateCreated'] = 'now_usec()';
            
            $this->TableName = "cardtransactions";
            
            $this->StartTransaction();
            
            $this->Insert($arrEntries);
            
            try
            {
                if(!App::HasError())
                {
                    $this->CommitTransaction();
                    return true;
                }
                else
                {
                    $this->RollBackTransaction ();
                    return false;
                }
            }
            catch(Exception $e)
            {
                $this->RollBackTransaction();
                return false;
            }
            
        }
        
        if(!$this->GetCardInfo( $cardnumber ))
        {
            return false;
        }
    }
    
    public function GetCardInfo( $cardnumber )
    {
        App::LoadModuleClass("Loyalty", "MemberCards");
        $_MemberCards = new MemberCards();
        
        $result = $_MemberCards->getMemberCardInfoByCard( $cardnumber );
        
        if(count( $result ) > 0)
        {
            return array('MID'=>$result[0]['MID'],
                         'CardID'=>$result[0]['CardID']);
        }
        else
        {
            return false;
        }
    }
    
    public function GetPointsConversion()
    {
        $this->TableName = "ref_pointsconversion";
        $where = " WHERE Status = 1";
        return parent::SelectByWhere($where);
        
    }
    
    public function GetPoints( $cardid )
    {
        $query = "SELECT SUM(Points) As Total
                   FROM cardtransactions
                  WHERE CardID = '$cardid'";
        
        return parent::RunQuery($query);
        
    }
}
?>
