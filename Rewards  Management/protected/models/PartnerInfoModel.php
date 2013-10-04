<?php

class PartnerInfoModel extends CFormModel
{
    public function getPartnerEmail($partnerpid){
        
        $connection = Yii::app()->db;
         
        $sql="SELECT Email FROM partnersinfo WHERE PartnerPID = :partnerpid";
        $command = $connection->createCommand($sql);
        $command->bindValue(':partnerpid', $partnerpid);
        $result = $command->queryAll();
         
        return $result;
        
    }
    /**
     * Get the Contact Person/s' Email by Company
     * @param int $companyID ID of the Partner (PartnerID)
     * @return array Array of email address
     * @author Mark Kenneth Esguerra
     * @date October 3, 2013
     */
    public function getPartnerEmailByCompany($companyID)
    {
        $connection = Yii::app()->db;
         
        $sql="SELECT a.Email FROM partnersinfo a
              INNER JOIN partners b ON a.PartnerPID = b.PartnerPID
              INNER JOIN ref_partners c ON b.RefPartnerID = c.PartnerID
              WHERE c.PartnerID = :companyid
             ";
        $command = $connection->createCommand($sql);
        $command->bindValue(':companyid', $companyID);
        $result = $command->queryAll();
         
        return $result;
    }
}
?>
