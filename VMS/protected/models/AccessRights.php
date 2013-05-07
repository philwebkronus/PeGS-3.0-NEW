<?php

/**
 * @author owliber
 * @date Oct 18, 2012
 * @filename AccessRights.php
 * 
 */

class AccessRights extends CFormModel
{
    /**
     * 
     * @param int $accounttypeid
     * @return string
     */
    public function getAccessRights($accounttypeid)
    {
        $query = "SELECT * FROM accessrights
                  WHERE AccountTypeID =:accounttypeid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":accounttypeid", $accounttypeid);
        return $sql->queryAll();
        
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @param int $menuid
     * @throws Exception
     */
    public function setAccessRightMenu($accounttypeid,$menuid)
    {
        
        $conn = Yii::app()->db;
             
        $query = "INSERT INTO accessrights (AccountTypeID,MenuID)
                  VALUES (:accounttypeid,:menuid)";
        
        $sql = $conn->createCommand($query);
        $sql->bindParam(":accounttypeid", $accounttypeid);
        $sql->bindParam(":menuid", $menuid);
        
        $trx = $conn->beginTransaction();
        
        try
        {
            $sql->execute();
            $trx->commit();
        }
        catch(Exception $e)
        {
            $trx->rollback();
            throw new Exception($e->getMessage());
        }
        
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @param int $submenuid
     * @throws Exception
     */
    public function setAccessRightSubMenu($accounttypeid,$submenuid)
    {
        
        $conn = Yii::app()->db;
        
        $menuid = SubMenu::getMenuID($submenuid);
             
        $query = "INSERT INTO accessrights (AccountTypeID,MenuID,SubMenuID)
                  VALUES (:accounttypeid,:menuid,:submenuid)";
        
        $sql = $conn->createCommand($query);
        $sql->bindParam(":accounttypeid", $accounttypeid);
        $sql->bindParam(":menuid", $menuid);
        $sql->bindParam(":submenuid", $submenuid);
        
        $trx = $conn->beginTransaction();
        
        try
        {
            $sql->execute();
            $trx->commit();
        }
        catch(Exception $e)
        {
            $trx->rollback();
            throw new Exception($e->getMessage());
        }
        
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @param int $menuid
     * @throws Exception
     */
    public function removeMenuRights($accounttypeid,$menuid)
    {
        $conn = Yii::app()->db;
        
        $menus = implode(',',$menuid);
        
        $query = "DELETE FROM accessrights
              WHERE MenuID NOT IN (:menus)
               AND AccountTypeID =:accounttypeid";

        $sql = $conn->createCommand($query);
        $sql->bindParam(":menus", $menus);
        $sql->bindParam(":accounttypeid", $accounttypeid);
        
        $trx = $conn->beginTransaction();
        
        try
        {
            $sql->execute();
            $trx->commit();
        }
        catch (bException $e)
        {
            $trx->rollback();
            throw new Exception($e->getMessage());
        }         
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @param int $submenuid
     * @throws Exception
     */
    public function removeSubMenuRights($accounttypeid, $submenuid)
    {
        $conn = Yii::app()->db;
        
        $submenus = implode(',',$submenuid);
        
        $query = "DELETE FROM accessrights
              WHERE SubMenuID IN (:submenus)
               AND AccountTypeID =:accounttypeid";

        $sql = $conn->createCommand($query);
        //$sql->bindParam(":menus", $menus);
        $sql->bindParam(":submenus", $submenus);
        $sql->bindParam(":accounttypeid", $accounttypeid);
        
        $trx = $conn->beginTransaction();
        
        try
        {
            $sql->execute();          
            $trx->commit();
        }
        catch (Exception $e)
        {
            $trx->rollback();
            throw new Exception($e->getMessage());
        }
        
                
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @param int $menuid
     * @return boolean
     */
    public function checkMenuAccess($accounttypeid,$menuid)
    {
        $query = "SELECT * FROM accessrights
                  WHERE MenuID =:menuid
                    AND AccountTypeID =:accounttypeid";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":menuid", $menuid);
        $sql->bindParam(":accounttypeid", $accounttypeid);
        $result = $sql->queryAll();
        
        if(count($result)>0)
        {
            return true;
        }
        else
            return false;
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @param int $submenuid
     * @return boolean
     */
    public function checkSubMenuAccess($accounttypeid,$submenuid)
    {
        $query = "SELECT * FROM accessrights
                  WHERE SubMenuID =:submenuid
                    AND AccountTypeID =:accounttypeid";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":submenuid", $submenuid);
        $sql->bindParam(":accounttypeid", $accounttypeid);
        $result = $sql->queryAll();
        
        if(count($result)>0)
        {
            return true;
        }
        else
            return false;
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @return int menuid
     */
    public function getDefaultPage($accounttypeid)
    {
        $query = "SELECT MenuID FROM accessrights
                  WHERE AccountTypeID =:accounttype
                  AND IsDefaultPage = 1";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":accounttype", $accounttypeid);
        $result = $sql->queryRow();
        
        if(count($result) > 0)
        {
            return $result['MenuID'];  
        }
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @param int $menuid
     * @throws Exception
     */
    public static function setDefaultPage($accounttypeid,$menuid)
    {
        $conn = Yii::app()->db;
        $trx = $conn->beginTransaction();
        
        AccessRights::resetDefaultPage($accounttypeid);
        
        $query = "UPDATE accessrights
                  SET IsDefaultPage = 1
                  WHERE AccountTypeID =:accounttype
                    AND MenuID =:menuid
                  LIMIT 1";
        
        $sql = $conn->createCommand($query);
        $sql->bindParam(":accounttype", $accounttypeid);
        $sql->bindParam(":menuid", $menuid);
        $result = $sql->execute();
        
        if($result > 0)
        {
            try
            {
                $trx->commit();
            }
            catch(Exception $e)
            {
                $trx->rollback();
                throw new Exception($e->getMessage());
                
            }
        }
        
    }
    
    /**
     * 
     * @param int $accounttypeid
     */
    public function resetDefaultPage($accounttypeid)
    {
        $query = "UPDATE accessrights
                  SET IsDefaultPage = 0
                  WHERE AccountTypeID =:accounttype
                    AND IsDefaultPage = 1";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":accounttype", $accounttypeid);
        $sql->execute();
        
    }
    
    /**
     * 
     * @param int $accounttypeid
     * @return string link
     */
    public function getDefaultPageURL($accounttypeid)
    {
        $defaultPage = AccessRights::getDefaultPage($accounttypeid);

        if(!empty($defaultPage))
        {
            $query = "SELECT Link FROM menus
                  WHERE MenuID =:menuid";
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindParam(":menuid", $defaultPage);
            $result = $sql->queryRow();
            $link = $result['Link'];

            if(!empty($link))
            {
                return $link;
            }
            else
            {
                $query = null;
                $query = "SELECT sm.Link FROM submenus sm
                            INNER JOIN accessrights ar ON sm.SubMenuID = ar.SubMenuID
                          WHERE sm.MenuID =:menuid";
                $sql = Yii::app()->db->createCommand($query);
                $sql->bindParam(":menuid", $defaultPage);
                $result = $sql->queryAll();
                $link = $result[0]['Link'];

                return $link;

            }
        }
        
    }
}
?>
