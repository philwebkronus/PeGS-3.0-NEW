<?php

/**
 * @author owliber
 * @date Nov 12, 2012
 * @filename VMSUserAccessRights.php
 * 
 */

class VMSUserAccessRights extends CWebUser
{
    public function allowedUsers()
    {
        if(!Yii::app()->user->isGuest && Yii::app()->user->isAllowedUser())
        {
            return array(Yii::app()->user->getName());
        }
        else
            return array('');
    }
    
    public function isAllowedUser()
    {
        
        if($this->hasSubMenuAccess()==true)
        {
            return true;
        }else
            return false;

    }
        
    public function isAdmin()
    {
        if($this->getAccountTypeID() == 1)
            return true;
        else
            return false;
    }
    
    public function hasSubMenuAccess()
    {
        return AccessRights::checkSubMenuAccess($this->getAccountTypeID(), $this->getAccessSubMenuID());
        
    }
    
    public function getAccountTypeID()
    {
        return Yii::app()->session['AccountType'];
    }
    
    public function getAccessSubMenuID()
    {
        return SubMenu::getSubMenuIDByLink($this->getControllerAction());
    }
        
    public function getControllerAction()
    {
        return Yii::app()->controller->getUniqueId() .'/'. Yii::app()->controller->action->id;
    }
    
    public function getUserSiteInfo()
    {
        return Utilities::getSiteInfo();
    }
    
    public function getSiteID()
    {
        $siteinfo = $this->getUserSiteInfo();
        return $siteinfo[0]['SiteID'];
    }
    
    public function getSiteCode()
    {
        $siteinfo = $this->getUserSiteInfo();
        return $siteinfo[0]['SiteCode'];
    }
    
    public function isPerSite()
    {
        /**
         * Get account group access from Operator, Supervisor & Cashier
         */
        if(Yii::app()->user->getAccountTypeID() == 2 || Yii::app()->user->getAccountTypeID() == 3 || Yii::app()->user->getAccountTypeID() == 4)
            return true;
        else
            return false;
    }
  
}
?>
