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
        $query = "SELECT * FROM members WHERE TemporaryAccountCode = '$TempAccountCode'";
        
        $result = parent::RunQuery($query);
        
        return $result;
    }
   

}

?>
