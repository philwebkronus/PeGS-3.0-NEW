<?php

/**
 * @author owliber
 * @date Nov 5, 2012
 * @filename SubMenu.php
 * 
 */

class SubMenu extends CFormModel
{
    CONST STATUS_ACTIVE = 1;
    CONST STATUS_INACTIVE = 0;
    
    public function rules()
    {
        return array(
            array('Name, Description, Link, Status','required'),
        );
    }
    
    public static function getSubMenusByAccountType($menuid,$accounttype)
    {       
            $query = "SELECT DISTINCT (sm.SubMenuID)
                                    , sm.Name
                                    , sm.Link
                      FROM
                        submenus sm
                      INNER JOIN accessrights ar
                      ON sm.SubMenuID = ar.SubMenuID
                      WHERE
                        sm.MenuID =:menuid AND
                        ar.AccountTypeID =:accounttype
                      AND sm.Status = 1
                      ORDER BY sm.SortOrder;";
            
            $sql = Yii::app()->db->createCommand($query);
            $sql->bindParam(":menuid", $menuid);
            $sql->bindParam(":accounttype", $accounttype);
            $result = $sql->queryAll();
                        
            $items = array();

            if(empty($result))
                    return $items;

            foreach($result AS $row)
            {
                $items[] = array(
                   'label'         => $row['Name'],
                   'url'           => array($row['Link']),
                   'itemOptions'   => array('class'=>'subMenuItem'),
                   'linkOptions'   => array('class'=>'subMenuItemLink', 'title'=>$row['Name']),
                   'submenuOptions'=> array(),
                 );
            }

            return $items;
    }
    
    public static function getAllSubMenus($menuid)
    {               
        $query = "SELECT DISTINCT (sm.SubMenuID) `SubMenuID`
                                , sm.Name
                                , sm.Link
                  FROM
                    submenus sm
                  LEFT JOIN accessrights ar
                  ON sm.SubMenuID = ar.SubMenuID
                  WHERE
                    sm.MenuID =:menuid
                    AND sm.Status = 1";

        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":menuid", $menuid);
        $result = $sql->queryAll();

        return $result;
    }
    
    public function getAllAvailableSubMenus()
    { 
        $query = "SELECT sm.MenuID, m.Name AS `Menu`, sm.SubMenuID, sm.Name AS `Submenu`, sm.Link,
                  CASE sm.Status
                    WHEN 1 THEN 'Active'
                    WHEN 0 THEN 'Inactive'
                  END AS `Status`
                  FROM submenus sm
                    INNER JOIN menus m ON sm.MenuID = m.MenuID";
        
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
       
        return $result;
    }
    
    public function getMenuID($submenuid)
    {
        $query = "SELECT MenuID FROM submenus WHERE SubMenuID =:submenuid";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":submenuid", $submenuid);
        $result = $sql->queryRow();
        return $result['MenuID'];
    }
    
    public function getSubMenuStatus()
    {
        return array(
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
        );
    }
    
    public function insertSubMenu($submenu)
    {
        
        $conn = Yii::app()->db;        
        
        $trx = $conn->beginTransaction();
        
        $menuid = $submenu['MenuID'];
        $name = $submenu['Submenu']; 
        $link = $submenu['Link'];
        $desc = $submenu['Description'];
        $status = $submenu['Status'];
        
        $query = "INSERT INTO submenus (MenuID,Name,Link,Description,Status)
                  VALUES (:menuid, :name, :link, :desc, :status)";
        $sql = $conn->createCommand($query);
        $sql->bindParam(":menuid", $menuid);
        $sql->bindParam(":name", $name);
        $sql->bindParam(":link", $link);
        $sql->bindParam(":desc", $desc);
        $sql->bindParam(":status", $status);
                
        if(!$this->getSubMenuByName($name))
        {  
           try
           {
               $sql->execute();
               $trx->commit();
               
               return array('TransMsg'=>'New submenu creation is successful',
                            'TransCode'=>0);
               
               
           }
           catch (CDbException $e)
           {
               $trx->rollback();
               
               return array('TransMsg'=>'Error ' . $e->getMessage(),
                            'TransCode'=>1);
           }
        }
        else
        {
            return array('TransMsg'=>'Duplicate submenu entry.',
                         'TransCode'=>2);
        }
        
    }
    
    public function getSubMenuByID($submenuid)
    {
        $query = "SELECT * FROM submenus
                  WHERE SubMenuID =:submenuid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":submenuid", $submenuid);
        $result = $sql->queryRow();
        
        return $result;
    }
    
    public function getSubMenuByName($name)
    {
        $query = "SELECT * FROM submenus
                  WHERE Name =:name";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":name", $name);
        $result = $sql->queryAll();
        
        if(count($result)> 0)
        {
            return true;
        }else
            return false;
    }
    
    public function updateSubMenuByID($submenu)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        $menuid = $submenu['MenuID'];
        $submenuid = $submenu['SubMenuID'];
        $name = $submenu['Name'];
        $link = $submenu['Link'];
        $desc = $submenu['Description'];
        $status = $submenu['Status'];
        
        $query = "UPDATE submenus
                  SET MenuID =:menuid,
                      Name =:name,
                      Link =:link,
                      Description =:desc,
                      Status =:status
                  WHERE SubMenuID =:submenuid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":name", $name);
        $sql->bindParam(":link", $link);
        $sql->bindParam(":desc", $desc);
        $sql->bindParam(":status", $status);
        $sql->bindParam(":submenuid", $submenuid);
        $sql->bindParam(":menuid", $menuid);
        $result = $sql->execute();
        
        if($result > 0)
        {
           try
            {

                $trx->commit();
                
                return array('TransMsg'=>'Sub Menu is successfully updated.',
                             'TransCode'=>0);
                
            }
            catch(CDbException $e)
            {
                $trx->rollback();
                
                return array('TransMsg'=>'Error '. $e->getMessage(),
                             'TransCode'=>1);
            } 
        }
        else
        {
            return array('TransMsg'=>'No record was updated.',
                         'TransCode'=>2);
        }
        
        
    }
    
    public function deleteSubMenuByID($submenuid)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        $query = "DELETE FROM submenus WHERE SubMenuID =:submenuid";
        $sql = $conn->createCommand($query);
        $sql->bindParam(":submenuid", $submenuid);
        $result = $sql->execute();
        
        
        if($result > 0)
        {
            try
            {
                $trx->commit();
                return array('TransMsg'=>'Sub Menu was successfully deleted.',
                             'TransCode'=>0);
            }
            catch(Exception $e)
            {
                $trx->rollback();
                return array('TransMsg'=>'Sub Menu deletion was failed.',
                         'TransCode'=>1);
            }
            
        }
    }
    
    public function changeMenuStatusByID($submenuid,$status)
    {
        $conn = Yii::app()->db;
        
        $status = $status == 0 || $status == NULL || empty($status) ? 1 : 0;
        
        $trx = $conn->beginTransaction();
        
        $query = "UPDATE submenus SET Status =:status
                  WHERE SubMenuID =:submenuid";
        $sql = $conn->createCommand($query);
        $sql->bindParam(":submenuid",$submenuid);
        $sql->bindParam(":status",$status);
                
        $result = $sql->execute();
        
        if($result > 0)
        {
            try
            {
                $trx->commit();
                return array('TransMsg'=>'Sub Menu was status was successfully changed.',
                                'TransCode'=>0);
            }
            catch(Exception $e)
            {
                $trx->rollback();
                return array('TransMsg'=>'Change status failed.',
                            'TransCode'=>0);
            }
        }
        
    }
    
    public function getSubMenuIDByLink($link)
    {
        $query = "SELECT SubMenuID FROM submenus WHERE Link =:link";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":link", $link);
        $result = $sql->queryRow();
                
         return $result['SubMenuID'];
    }
}
?>
