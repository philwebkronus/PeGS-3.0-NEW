<?php

class SiteMenu extends CFormModel
{
    public static function getMenusByAccountType($accounttype)
    {
        
        $query = "SELECT DISTINCT(m.MenuID) AS MenuID, m.Name, m.Link
                    FROM
                      menus m
                    INNER JOIN accessrights ar
                    ON m.MenuID = ar.MenuID
                    WHERE
                      ar.AccountTypeID =:accounttype
                    AND m.Status = 1
                    ORDER BY m.MenuID;";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":accounttype", $accounttype);
        $result = $sql->queryAll();
      
        $ctr = 0;
        foreach($result AS $row)
        {
            // Add the menu
            $items[$ctr] = array(
                        'label'         => $row['Name'],
                        'url'           => array($row['Link']),
            );
            
            // Query for the submenus
            $query2 = "SELECT sb.Name, sb.Link FROM submenus sb
                        INNER JOIN accessrights ar ON sb.SubMenuID = ar.SubMenuID
                        WHERE ar.MenuID = :menuID AND ar.AccountTypeID = :accounttype";
            $sql2 = Yii::app()->db->createCommand($query2);
            $sql2->bindParam(":menuID", $row['MenuID']);
            $sql2->bindParam(":accounttype", $accounttype);
            
            $result2 = $sql2->queryAll();
            
            $subMenu = array();
            if (count($result2) > 0)
            {   
                foreach ($result2 AS $row2)
                {
                    $subMenu[] = array('label'=>$row2['Name'], 'url'=>array($row2['Link']));
                }
                $items[$ctr]['items'] = $subMenu;
            }
            
            $ctr++;
                 
        }
        
        if(empty($items))
            $items = array();
        
        return $items; 
        
    }
    
    /**
     * @modifiedBy: Noel Antonio
     * @dateModified: November 12, 2013
     * @description: Join submenus in the query for landing page.
     * @param int $accounttype account type logged-in
     * @return string landing page
     */
    public static function getLandingPage($accounttype)
    {
        
        $query = "SELECT 
                        m.MenuID, m.Name, m.Link AS MenuLink,
                        ar.SubMenuID, sm.Name, sm.Link AS SubMenuLink
                    FROM menus m
                    LEFT JOIN
                        submenus sm ON m.MenuID = sm.MenuID
                    INNER JOIN 
                        accessrights ar ON m.MenuID = ar.MenuID
                    WHERE
                        ar.AccountTypeID =:accounttype
                    AND m.Status = 1
                    ORDER BY m.MenuID LIMIT 1 ;";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":accounttype", $accounttype);
        $result = $sql->queryAll();
        
        if (count($result) > 0)
        {
            foreach($result AS $row)
            {
                if (!empty($row['MenuLink']))
                    $link = $row['MenuLink'];
                else
                    $link = $row['SubMenuLink'];
            }
        }
        else
        {
            $link = array();
        }
        return $link; 
        
    }
}
?>