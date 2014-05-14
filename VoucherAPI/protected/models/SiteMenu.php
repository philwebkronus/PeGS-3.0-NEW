<?php

/**
 * @author owliber
 * @date Oct 15, 2012
 * @filename SiteMenu.php
 * 
 */
?>
<?php
class SiteMenu extends CFormModel
{
    CONST STATUS_ACTIVE = 1;
    CONST STATUS_INACTIVE = 0;
    
    public $Name;
    public $Link;
    public $Description;
    public $Status;
    
    public function rules()
    {
        return array(
            array('Name','required'),
        );
    }
        
    public static function getMenusByAccountType($accounttype)
    {
        
        $query = "SELECT DISTINCT(m.MenuID), m.Name, m.Link
                    FROM
                      menus m
                    INNER JOIN accessrights ar
                    ON m.MenuID = ar.MenuID
                    WHERE
                      ar.AccountTypeID =:accounttype
                    AND m.Status = 1
                    ORDER BY m.SortOrder;";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":accounttype", $accounttype);
        $result = $sql->queryAll();
      
        foreach($result AS $row)
        {
            $menuid = $row["MenuID"];
            
            $childItems=SubMenu::getSubMenusByAccountType($menuid,$accounttype); 
                           
            $items[] = array(
                'label'         => $row['Name'],
                'url'           => array($row['Link']),
                'itemOptions'   =>  array('class'=>'menuItem'),
                'linkOptions'   =>  array('class'=>'menuItemLink', 'title'=>$row['Name']),
                'submenuOptions'=> array('id'=>'submenu',),
                'items'         => $childItems,
            );
                 
        }
        
        if(empty($items))
            $items = array();
        
        return $items; 
        
    }
    
    public function getAllMenus()
    {
        $query = "SELECT DISTINCT(m.MenuID) as `id`, m.MenuID, m.Name,m.Link 
                  FROM menus m
                  LEFT JOIN accessrights ar ON m.MenuID = ar.MenuID
                  WHERE m.Status = 1";
        
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
                  
        return $result;
        
    }
        
    public function getMenus()
    {
        $query = "SELECT MenuID,Name
                  FROM menus
                  WHERE Status = 1
                  ORDER BY 2";
        
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
                  
        return $result;
        
    }
    
    public function getMenuList()
    {
       $menus = CHtml::listData(SiteMenu::getMenus(),'MenuID','Name');
       return $menus;
    }
           
    public function getAllAvailableMenus()
    { 
        $query = "SELECT MenuID, Name, Link,
                  CASE Status
                    WHEN 1 THEN 'Active'
                    WHEN 0 THEN 'Inactive'
                  END AS `Status`
                  FROM menus";
        
        $sql = Yii::app()->db->createCommand($query);
        $result = $sql->queryAll();
       
        return $result;
    }
        
    public function getMenuByID($menuid)
    {
        $query = "SELECT * FROM menus
                  WHERE MenuID =:menuid";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":menuid", $menuid);
        $result = $sql->queryRow();
        
        return $result;
    }
    
    public function getMenuByName($name)
    {
        $query = "SELECT * FROM menus
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
    
    public function updateMenuByID($menu)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        $menuid = $menu['MenuID'];
        $name = $menu['Name'];
        $link = $menu['Link'];
        $desc = $menu['Description'];
        $status = $menu['Status'];
        
        $query = "UPDATE menus
                  SET Name =:name,
                      Link =:link,
                      Description =:desc,
                      Status =:status
                  WHERE MenuID =:menuid";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":name", $name);
        $sql->bindParam(":link", $link);
        $sql->bindParam(":desc", $desc);
        $sql->bindParam(":status", $status);
        $sql->bindParam(":menuid", $menuid);
        $result = $sql->execute();
        
        if($result > 0)
        {
           try
            {

                $trx->commit();
                
                return array('TransMsg'=>'Menu is successfully updated.',
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
    
    public function insertMenu($menu)
    {
        
        $conn = Yii::app()->db;        
        
        $trx = $conn->beginTransaction();
        
        $name = $menu['Name'];
        $link = $menu['Link'];
        $desc = $menu['Description'];
        $status = $menu['Status'];
        
        $query = "INSERT INTO menus (Name,Link,Description,Status)
                  VALUES (:name,:link,:desc,:status)";
        $sql = $conn->createCommand($query);
        $sql->bindParam(":name", $name);
        $sql->bindParam(":link", $link);
        $sql->bindParam(":desc", $desc);
        $sql->bindParam(":status", $status);
                
        if(!$this->getMenuByName($name))
        {           
           
           try
           {
               $sql->execute();
               $trx->commit();
               
               return array('TransMsg'=>'New menu creation is successful',
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
            return array('TransMsg'=>'Duplicate menu entry.',
                         'TransCode'=>2);
        }
        
    }
    
    public function deleteMenuByID($menuid)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        $query = "DELETE FROM menus WHERE MenuID =:menuid";
        $sql = $conn->createCommand($query);
        $sql->bindParam(":menuid", $menuid);
        $result = $sql->execute();
        
        
        if($result > 0)
        {
            try
            {
                $trx->commit();
                return array('TransMsg'=>'Menu was successfully deleted.',
                         'TransCode'=>0);
            }
            catch(Exception $e)
            {
                $trx->rollback();
                return array('TransMsg'=>'Menu deletion was failed.',
                         'TransCode'=>1);
            }
            
        }
    }
    
    public function changeMenuStatusByID($menuid,$status)
    {
        $conn = Yii::app()->db;
        
        $status = $status == 0 || $status == NULL || empty($status) ? 1 : 0;
        
        $trx = $conn->beginTransaction();
        
        $query = "UPDATE menus SET Status =:status
                  WHERE MenuID =:menuid";
        $sql = $conn->createCommand($query);
        $sql->bindParam(":menuid",$menuid);
        $sql->bindParam(":status",$status);
                
        $result = $sql->execute();
        
        if($result > 0)
        {
            try
            {
                $trx->commit();
                return array('TransMsg'=>'Menu was status was successfully changed.',
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
    
    public function getMenuStatus()
    {
        return array(
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ACTIVE => 'Active',
        );
    }
    
    public function getActiveMenusByAccountType($accounttype)
    {
        $query = "SELECT DISTINCT(m.MenuID) as MenuID
                    FROM
                      menus m
                    INNER JOIN accessrights ar
                    ON m.MenuID = ar.MenuID
                    WHERE
                      ar.AccountTypeID =:accounttype
                    AND m.Status = 1
                    ORDER BY m.SortOrder;";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":accounttype", $accounttype);
        $result = $sql->queryAll();
        
        return $result[0]['MenuID'];
    }
}

?>