<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */

class Members extends BaseEntity
{

    function Members()
    {
    
        $this->ConnString = "membership";
        $this->TableName = "members";
        $this->Identity = "MID";
    
    }

    
    function Migrate($arrMemberstable,$arrMemberInfo)
    {

        $this->StartTransaction();

        try 
        {
            App::LoadCore('Randomizer.class.php');
            $randomizer = new Randomizer(); 
            $password = $randomizer->GenerateAlphaNumeric(8);     
            $arrMemberstable['Password'] = $password;
            //App::PR($Memberstable);
            $this->Insert($arrMemberstable);        

            if(!App::HasError())
            {
                App::LoadModuleClass("Membership", "MemberInfo");
                $MemberInfo = new MemberInfo();
                $MemberInfo->PDODB = $this->PDODB;
                $arrMemberInfo['MID'] = $this->LastInsertID;
                $MemberInfo->Insert($arrMemberInfo);
                if(!App::HasError())
                {
                    $this->CommitTransaction();
                }
                else
                {
                    $this->RollBackTransaction();
                }

                App::pr($MemberInfo);
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
    //DO NOT DELETE!!! -ish. PLEASEEEE!!!
    function getMID($UserName){
        $query = "Select MID, Password from members where UserName = '$UserName'";
      return parent::RunQuery($query);
    }
    
 
  
}

?>
