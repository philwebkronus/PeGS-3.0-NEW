<?php

class SiteMenu extends CFormModel
{
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
                    ORDER BY m.MenuID;";
        
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindParam(":accounttype", $accounttype);
        $result = $sql->queryAll();
      
        foreach($result AS $row)
        {
                           
            $items[] = array(
                'label'         => $row['Name'],
                'url'           => array($row['Link']),
            );
                 
        }
        
        if(empty($items))
            $items = array();
        
        return $items; 
        
    }
    
    
    public static function getLandingPage($accounttype)
    {
        
        $query = "SELECT DISTINCT(m.MenuID), m.Name, m.Link
                    FROM
                      menus m
                    INNER JOIN accessrights ar
                    ON m.MenuID = ar.MenuID
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
               $link = $row['Link'];
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