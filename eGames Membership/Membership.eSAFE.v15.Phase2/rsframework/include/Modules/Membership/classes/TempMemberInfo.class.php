<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */

class TempMemberInfo extends BaseEntity
{

    function TempMemberInfo()
    {
        $this->TableName = "memberinfo";
        $this->ConnString = "tempmembership";
        $this->Identity = "MID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    public function getMembersByAccount( $TempAccountCode )
    {
        // CCT 12/08/2017 EDITED BEGIN
        //$query = "SELECT * FROM members WHERE TemporaryAccountCode LIKE BINARY '$TempAccountCode'";
        $query = "SELECT * FROM members WHERE TemporaryAccountCode LIKE '$TempAccountCode'";
        // CCT 12/08/2017 EDITED END
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
    
    public function checkExistingEmail( $email )
    {
        $query = "SELECT COUNT(MemberInfoID) AS COUNT FROM memberinfo WHERE Email = '$email'";
        
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
    
    public function deactivateAccount( $email , $newemail)
    {
        $query = "UPDATE memberinfo SET Status = 2, Email = '$newemail' WHERE Email = '$email'";
        
        $this->ExecuteQuery($query);
        if ($this->HasError) {
            App::SetErrorMessage($this->getError());
            return false;
        }
    }
    
    public function getMembersByMID( $MID )
    {
        $query = "SELECT * FROM memberinfo WHERE MID= '$MID'";
        
        $result = parent::RunQuery($query);
        
        return $result;
    }
    
    
}

?>
