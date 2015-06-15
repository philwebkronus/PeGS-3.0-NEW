<?php

/*
 * @author : owliber
 * @date : 2013-05-30
 */

class AccessRights extends BaseEntity
{
    public function AccessRights()
    {
        $this->TableName = "accessrights";
        $this->ConnString = "membership";
    }
    
    public function getAccessRights($accounttypeid)
    {
        $query = "SELECT
                    a.MenuID,
                    a.AccountTypeID,
                    m.Name,
                    m.Link                    
                  FROM membership.accessrights a
                    INNER JOIN membership.menus m ON a.MenuID = m.MenuID
                  WHERE a.AccountTypeID = $accounttypeid
                  AND a.Status = 1
                  GROUP BY a.MenuID
                  ORDER BY a.SortOrder
                  ;";
        
        return parent::RunQuery($query);
    }
    
    public function getDefaultPage($accounttypeid)
    {
        $query = "SELECT
                    m.Link
                  FROM membership.accessrights a
                    INNER JOIN membership.menus m ON a.MenuID = m.MenuID
                  WHERE a.AccountTypeID = $accounttypeid
                  AND a.`Default` = 1
                  AND a.Status = 1
                  ;";
        return parent::RunQuery($query);
        
    }
    
    public function getLandingSubPage($accounttypeid)
    {
       $query = "SELECT
                    s.Link
                  FROM membership.accessrights a
                    -- INNER JOIN membership.menus m ON a.MenuID = m.MenuID
                    INNER JOIN membership.submenus s ON a.SubMenuID = s.SubMenuID                    
                  WHERE a.AccountTypeID = $accounttypeid
                  AND a.`Default` = 1
                  AND a.Status = 1
                  ;";
        return parent::RunQuery($query);
    }
    
    public function getMenuID($page)
    {
        $query = "SELECT * FROM menus WHERE Link = '$page'";
        $result = parent::RunQuery($query);
        return $result[0]['MenuID'];
    }
    
    public function getSubMenuID($page)
    {
        $query = "SELECT * FROM submenus WHERE Link = '$page'";
        $result = parent::RunQuery($query);
        return $result[0]['SubMenuID'];
    }
    
    public function getAccessibleMenuID($accounttypeid)
    {
        $query = "SELECT
                    a.MenuID
                  FROM membership.accessrights a
                    INNER JOIN membership.menus m ON a.MenuID = m.MenuID
                  WHERE a.AccountTypeID = $accounttypeid
                  AND a.Status = 1;";
        
        $result = parent::RunQuery($query);
        
        foreach($result as $menuid)
        {
            $newrows[] = $menuid['MenuID'];
        }
        
        return $newrows;
    }
    
    public function getAccessibleSubMenuID($accounttypeid)
    {
        $query = "SELECT
                    s.SubMenuID
                  FROM membership.accessrights a
                    INNER JOIN membership.submenus s ON a.MenuID = s.MenuID
                  WHERE a.AccountTypeID = $accounttypeid
                  AND a.Status = 1
                    GROUP BY 1;";
        
        $result = parent::RunQuery($query);
        
        if(count($result) > 0)
        {
            foreach($result as $submenu)
            {
                $newrows[] = $submenu['SubMenuID'];
            }
            $retval = $newrows;
        }
        else
        {
            $retval = false;
        }
        
        return $retval;
    }
    
    public function getSubMenus($menuid, $accounttypeid)
    {
        $query = "SELECT * FROM submenus s
                    INNER JOIN accessrights a ON s.SubMenuID = a.SubMenuID
                    WHERE s.MenuID = $menuid
                    AND a.AccountTypeID = $accounttypeid
                    AND a.Status = 1 ORDER BY a.SortOrder ASC";
        
        return parent::RunQuery($query);
    }
}
?>
