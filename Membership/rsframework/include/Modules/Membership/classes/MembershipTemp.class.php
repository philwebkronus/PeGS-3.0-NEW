<?php

/**
      * @author JunJun S. Hernandez
      * @return object array
      * Class for Membership_Temp;
     */

class MembershipTemp extends BaseEntity {

    public function MembershipTemp() {
        $this->ConnString = 'tempmembership';
        $this->DatabaseType = DatabaseTypes::PDO;
        $this->TableName = "memberinfo";
        $this->Identity = "MID";
    }

    public function updateTempEmail($arrMemberInfo, $MID) {
        $Email = $arrMemberInfo['Email'];
        $query = "UPDATE membership_temp.memberinfo SET Email = '$Email' WHERE MID = '$MID'";
        return parent::ExecuteQuery($query);
    }

    public function updateTempMemberUsername($arrMemberInfo, $MID) {
        $Email = $arrMemberInfo['Email'];
        $Password = $arrMemberInfo['Password'];
        if($Password == ''){
            $query = "UPDATE membership_temp.members SET UserName = '$Email' WHERE MID = $MID";
        } else {
            $query = "UPDATE membership_temp.members SET UserName = '$Email', Password = '$Password' WHERE MID = $MID";
        }
        return parent::ExecuteQuery($query);
    }
    
    public function updateTempProfileDateUpdated($HiddenMID, $arrMemberInfo, $aid) {
        $arrMemberInfo['DateUpdated'] = 'now_usec()';
        $DateUpdated = $arrMemberInfo['DateUpdated'];
        $query = "UPDATE membership.memberinfo SET DateUpdated = '$DateUpdated',
                                     UpdatedByAID = '$aid'
                                     WHERE MID = '$HiddenMID'";
        parent::ExecuteQuery($query);
    }
    
    public function updateTempProfileDateUpdatedAdmin($HiddenMID, $arrMemberInfo, $aid) {
        $arrMemberInfo['DateUpdated'] = 'now_usec()';
        $DateUpdated = $arrMemberInfo['DateUpdated'];
        $query = "UPDATE membership.memberinfo SET DateUpdated = '$DateUpdated',
                                     UpdatedByAID = '$aid'
                                     WHERE MID = '$HiddenMID'";
        parent::ExecuteQuery($query);
    }
    
    public function updateTempProfileEmailAdmin($Email, $hdnEmail) {
        $query = "UPDATE membership_temp.memberinfo SET Email = '$Email' WHERE Email = '$hdnEmail'";
        return parent::ExecuteQuery($query);
    }
    
    public function updateTempMemberUsernameAdmin($Email, $hdnEmail) {
        $query = "UPDATE membership_temp.members SET UserName = '$Email' WHERE UserName = '$hdnEmail'";
        return parent::ExecuteQuery($query);
    }

    public function getMID($Email) {
        $query = "SELECT MID FROM membership_temp.members WHERE UserName = '$Email'";
        return parent::RunQuery($query);
    }
    
    public function checkEmailByMID($MID, $Email) {
        $query = "SELECT COUNT(MID) AS COUNT FROM membership_temp.members WHERE Email = '$Email' AND MID != $MID AND Status = 1 AND IsVerified = 1;";
        return parent::RunQuery($query);
    }
    
    public function checkIfEmailExists($Email) {
        $query = "SELECT COUNT(Email) AS COUNT FROM memberinfo WHERE Email = '$Email' AND Status = 1;";
        return parent::RunQuery($query);
    }
    
    public function checkIfEmailExistsWithMID($MID, $Email) {
        $query = "SELECT COUNT(Email) AS COUNT FROM memberinfo WHERE MID != $MID AND Email = '$Email' AND Status = 2;";
        return parent::RunQuery($query);
    }
    
    public function getTempEmailByMID() {
        $query = "SELECT Email FROM membership_temp.memberinfo WHERE MID = $MID;";
        return parent::RunQuery($query);
    }
    
}