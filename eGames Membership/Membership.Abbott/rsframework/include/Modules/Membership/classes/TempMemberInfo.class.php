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
        $query = "SELECT * FROM members WHERE TemporaryAccountCode LIKE BINARY '$TempAccountCode'";
        
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
    public function getEmailByMIDSP( $MID )
    {
        $query = "CALL membership.sp_select_data(0, 1, 0, $MID, 'Email', @RetCode, @RetMsg, @RetFiled)";
        
        $result = parent::RunQuery($query);
        
        $exp = explode(";", $result);
        
        return array(0 => array('Email' => $exp[0]));
    }

}

?>
