<?php

/*
 *Created By: Edson L. Perez
 *Created On: November 15, 2011
 *Purpose: DBQueries for Menu, Submenu, Access Rights Maintenance 
 */

include 'DbHandler.class.php';

class MenuMaintenance extends DBHandler
{
    public function __construct($sconectionstring)
    {
          parent::__construct($sconectionstring);
    }
    /**
     * Creates menu
     * @param string menu name
     * @param string default page
     * @param string description
     * @return int menu ID
     */
    function createmenu($zmenuname, $zdefaultpage, $zdescription)
    {
        $this->begintrans();
        $this->prepare("INSERT INTO menus(Name, Link, Description, Status) VALUES(?,?,?,1)");
        $this->bindparameter(1, $zmenuname);
        $this->bindparameter(2, $zdefaultpage);
        $this->bindparameter(3, $zdescription);
        try
        {
            $this->execute();
            $menuID = $this->insertedid();
            if($menuID > 0)
            {
                $this->committrans();
                return $menuID;
            }
            else
            {
                $this->rollbacktrans();
                return 0;
            }
        }
        catch(PDOException $e)
        {
            return 0;
            $this->rollbacktrans();
        }
    }
    
    /**
     * Jqgrid pagination - Counts all menus
     * @return count
     */
    function countmenu($zmenuID)
    {
        if($zmenuID > 0){
            $stmt = "SELECT COUNT(*) ctrmenu FROM menus WHERE MenuID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zmenuID);
        } else {
            $stmt = "SELECT COUNT(*) ctrmenu FROM menus";
            $this->prepare($stmt);
        }
        
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * Viewing of menu
     * @param int menu ID
     * @param int start and limit
     * @return array menu details
     */
    function viewmenu($zmenuID, $zstart, $zlimit, $zsort, $zdirection)
    {
        //jqgrid pagination
        if($zstart == null && $zlimit == null)
        {
            //get specific menu
            if($zmenuID > 0)
            {
               $stmt = "SELECT MenuID, Name, Link, Description, Status FROM menus WHERE MenuID = ?";
               $this->prepare($stmt);
               $this->bindparameter(1, $zmenuID);
            }
            //filtering of menus
            else
            {
                $stmt = "SELECT MenuID, Name, Link, Description, Status FROM menus";
                $this->prepare($stmt);
            }
        }
        else
        {
            //get specific menu
            if($zmenuID > 0)
            {
                $stmt = "SELECT MenuID, Name, Link, Description, Status FROM menus 
                         WHERE MenuID = ?
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
                $this->prepare($stmt);
                $this->bindparameter(1, $zmenuID);
            }
            else{
                $stmt = "SELECT MenuID, Name, Link, Description, Status FROM menus 
                         ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
                $this->prepare($stmt);
            }
        }
        
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * get active menu only
     * @return array active menus 
     */
    function getactivemenu()
    {
        $stmt = "SELECT MenuID, Name, Link, Description, Status FROM menus WHERE Status = 1";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * For updating of menus 
     * @param string menu name
     * @param string menu description
     * @param int menu ID
     * @return boolean 1 | 0
     */
    function updatemenu($zmenuname, $zdescription, $zmenuID)
    {
        $this->begintrans();
        $this->prepare("UPDATE menus SET Name = ?, Description = ? WHERE MenuID = ?");
        $this->bindparameter(1, $zmenuname);
        $this->bindparameter(2, $zdescription);
        $this->bindparameter(3, $zmenuID);
        $this->execute();
        $ctrrow = $this->rowCount();
        if($ctrrow > 0)
        {
            $this->committrans();
            return 1;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    /**
     * For viewing of sub-menu
     * @param int sub-menu ID
     * @param int menu ID
     * @param int start 
     * @param limit
     * @return array submenu data
     */
    function viewsubmenu($zsubmenuID, $zmenuID, $zstart, $zlimit, $zsort, $zdirection)
    {
        //if not jqgrid pagination
        if($zstart == null && $zlimit == null)
        {
            //on populating of comboboxes
            if($zmenuID > 0)
            {
                $stmt = "SELECT SubMenuID, Name FROM submenus WHERE MenuID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zmenuID);
            }
            else
            {
                //on updating sub-menus
                if($zsubmenuID > 0)
                {
                    $stmt = "SELECT sb.SubMenuID, sb.Name as SubMenuName, sb.Group, sb.Description, m.Name as MenuName, m.MenuID, sb.Status FROM submenus sb
                             INNER JOIN menus m ON m.MenuID = sb.MenuID WHERE sb.SubMenuID = ?";
                    $this->prepare($stmt);
                    $this->bindparameter(1, $zsubmenuID);
                }
            }
        }
        else
        {
                //on viewing of submenus, jqgrid
                if($zmenuID > 0)
                {
                    if($zsubmenuID > 0)
                    {
                         $stmt = "SELECT sb.SubMenuID, sb.Name as SubMenuName, sb.Group, sb.Description, m.Name as MenuName, sb.Status, sb.MenuID FROM submenus sb
                                  INNER JOIN menus m ON sb.MenuID = m.MenuID 
                                  WHERE sb.MenuID = ? AND sb.SubMenuID = ? 
                                  ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
                         $this->prepare($stmt);
                         $this->bindparameter(1, $zmenuID);
                         $this->bindparameter(2, $zsubmenuID);
                    } else {
                         $stmt = "SELECT sb.SubMenuID, sb.Name as SubMenuName, sb.Group, sb.Description, m.Name as MenuName, sb.Status, sb.MenuID FROM submenus sb
                             INNER JOIN menus m ON sb.MenuID = m.MenuID WHERE sb.MenuID = ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
                         $this->prepare($stmt);
                         $this->bindparameter(1, $zmenuID);
                    }
                }
                else
                {
                    $stmt = "SELECT sb.SubMenuID, sb.Name as SubMenuName, sb.Group, sb.Description, m.Name as MenuName, sb.Status, sb.MenuID FROM submenus sb
                             INNER JOIN menus m ON sb.MenuID = m.MenuID ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
                    $this->prepare($stmt);
                }
        }
        
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * counts number of submenu
     * @param int menu ID
     * @return int count 
     */
    function countsubmenu($zmenuID, $zsubmenuid)
    {
        //if filtered with a menu ID
        if($zmenuID > 0)
        {
            if($zsubmenuid > 0){
                $stmt = "SELECT COUNT(*) ctrsubmenu FROM submenus sb
                     INNER JOIN menus m ON sb.MenuID = m.MenuID WHERE sb.MenuID  = ? AND sb.SubMenuID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zmenuID);
                $this->bindparameter(2, $zsubmenuid);
            } else {
                $stmt = "SELECT COUNT(*) ctrsubmenu FROM submenus sb
                     INNER JOIN menus m ON sb.MenuID = m.MenuID WHERE sb.MenuID  = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zmenuID);
            }
        }
        else
        {
            if($zsubmenuid > 0){
               $stmt = "SELECT COUNT(*) ctrsubmenu FROM submenus sb
                     INNER JOIN menus m ON sb.MenuID = m.MenuID WHERE sb.SubMenuID = ?";
               $this->prepare($stmt); 
               $this->bindparameter(1, $zsubmenuid);
            } else {
               $stmt = "SELECT COUNT(*) ctrsubmenu FROM submenus sb
                     INNER JOIN menus m ON sb.MenuID = m.MenuID";
               $this->prepare($stmt); 
            }
        }
 
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * updates a particular sub-menu
     * @param int menu ID
     * @param string sub-menu name
     * @param string description
     * @param string group
     * @param int sub-menu ID
     * @return boolean 0 | 1
     */
    function updatesubmenu($zmenuID, $zsubmenuname, $zdescription, $zgroup, $zsubmenuID)
    {
        $this->begintrans();
        $this->prepare("UPDATE submenus SET `MenuID` = ?, `Name` = ?, `Description` = ?, `Group` = ? WHERE `SubMenuID` = ?");
        $this->bindparameter(1, $zmenuID);
        $this->bindparameter(2, $zsubmenuname);
        $this->bindparameter(3, $zdescription);
        $this->bindparameter(4, $zgroup);
        $this->bindparameter(5, $zsubmenuID);
        $this->execute();
        $ctrrow = $this->rowCount();
        if($ctrrow > 0)
        {
            $this->committrans();
            return 1;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    /**
     * Adding a sub-menu
     * @param int menu ID
     * @param string menu name
     * @param string description
     * @param string group
     * @return: if successfull this will return ID otherwise 0 or false; 
     */
    function insertsubmenu($zmenuID, $zsubmenuname, $zdescription, $zgroup, $zstatus)
    {
        $this->begintrans();
        $stmt = "INSERT INTO submenus(`MenuID`, `Name`, `Description`, `Group`, `Status`) VALUES (?,?,?,?,?)";
        $this->prepare($stmt);
        $this->bindparameter(1, $zmenuID);
        $this->bindparameter(2, $zsubmenuname);
        $this->bindparameter(3, $zdescription);
        $this->bindparameter(4, $zgroup);
        $this->bindparameter(5, $zstatus);
        if($this->execute())
        {
            $submenuID = $this->insertedid();
            
            $this->committrans();
            return $submenuID;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    /**
     * Get Account Types
     *@param string order by
     *@return array list of account types
     */
    function getaccounttypes($zorderby)
    {
        $stmt = "SELECT AccountTypeID, Name FROM ref_accounttypes ORDER BY ".$zorderby." ASC";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * Assign a access right to a certain submenu
     * @param: (int)accounttypeID, (int)menuID, (int)submenuID, (int)orderID, (str)url, (str)url2
     * @return: if successfull this will return ID otherwise 0 or false; 
     */
    function createaccessrights($zacctypeID, $zmenuID, $zsubmenuID, $zorderID, $zurl, $zurl2)
    {
        $this->begintrans();
        $this->prepare("INSERT INTO accessrights(AccountTypeID, MenuID, SubMenuID, OrderID, DefaultURL, DefaultURL2) VALUES (?,?,?,?,?,?)");
        $this->bindparameter(1, $zacctypeID);
        $this->bindparameter(2, $zmenuID);
        $this->bindparameter(3, $zsubmenuID);
        $this->bindparameter(4, $zorderID);
        $this->bindparameter(5, $zurl);
        $this->bindparameter(6, $zurl2);
        try
        {
            $this->execute();
            $rightID = $this->insertedid();
            if($rightID > 0)
            {
                $this->committrans();
                return $rightID;
            }
            else
            {
                $this->rollbacktrans();
                return 0;
            }
        }
        catch (PDOException $e)
        {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    /**
     *
     * @param int account type ID nullable
     * @param int start, limit nullable;
     * @return array accessrights info 
     */
    function viewaccessrights($zaccounttypeID, $zsort, $zdirection, $zstart, $zlimit)
    {
        if($zstart == null && $zlimit == null)
        {
            if($zaccounttypeID > 0)
            {
                $stmt = "SELECT ar.ID as AccessRightsID, ar.AccountTypeID, ar.MenuID, ar.SubMenuID, ar.OrderID, 
                         ar.DefaultURL, ar.DefaultURL2, ra.Name as AccountType, m.Name as MenuName, m.Link as MenuLink,
                         sm.Name as SubMenuName, sm.Group FROM accessrights ar
                         INNER JOIN ref_accounttypes ra ON ar.AccountTypeID = ra.AccountTypeID
                         INNER JOIN menus m ON ar.MenuID = m.MenuID
                         LEFT JOIN submenus sm ON ar.SubMenuID = sm.SubMenuID WHERE ar.AccountTypeID = ?";
                $this->prepare($stmt);
                $this->bindparameter(1, $zaccounttypeID);
            }
            else
            {
                $stmt = "SELECT ar.ID as AccessRightsID, ar.AccountTypeID, ar.MenuID, ar.SubMenuID, ar.OrderID, 
                         ar.DefaultURL, ar.DefaultURL2, ra.Name as AccountType, m.Name as MenuName, m.Link as MenuLink,
                         sm.Name as SubMenuName, sm.Group FROM accessrights ar
                         INNER JOIN ref_accounttypes ra ON ar.AccountTypeID = ra.AccountTypeID
                         INNER JOIN menus m ON ar.MenuID = m.MenuID
                         LEFT JOIN submenus sm ON ar.SubMenuID = sm.SubMenuID 
                         WHERE m.Status = 1
                         ORDER BY MenuID,OrderID ASC";
                $this->prepare($stmt);
            }
        }
        else
        {
            if($zaccounttypeID > 0)
            {
                $stmt = "SELECT ar.ID as AccessRightsID, ar.AccountTypeID, ar.MenuID, ar.SubMenuID, ar.OrderID, ar.DefaultURL, ar.DefaultURL2, ra.Name as AccountType, m.Name as MenuName, sm.Name as SubMenuName FROM accessrights ar
                         INNER JOIN ref_accounttypes ra ON ar.AccountTypeID = ra.AccountTypeID
                         INNER JOIN menus m ON ar.MenuID = m.MenuID
                         LEFT JOIN submenus sm ON ar.SubMenuID = sm.SubMenuID WHERE ar.AccountTypeID = ? ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                $this->prepare($stmt);
                $this->bindparameter(1, $zaccounttypeID);
            }
            else
            {
                $stmt = "SELECT ar.ID as AccessRightsID, ar.AccountTypeID, ar.MenuID, ar.SubMenuID, ar.OrderID, ar.DefaultURL, ar.DefaultURL2, ra.Name as AccountType, m.Name as MenuName, sm.Name as SubMenuName FROM accessrights ar
                         INNER JOIN ref_accounttypes ra ON ar.AccountTypeID = ra.AccountTypeID
                         INNER JOIN menus m ON ar.MenuID = m.MenuID
                         LEFT JOIN submenus sm ON ar.SubMenuID = sm.SubMenuID ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.",".$zlimit."";
                $this->prepare($stmt);
            }
        }
        
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * count access rights; jqgrid pagination
     * @param int account type ID nullable
     * @return int count
     */
    function countaccessrights($zaccounttypeID)
    {
        if($zaccounttypeID > 0)
        {
            $stmt = "SELECT COUNT(*) as ctraccess FROM accessrights ar
                     INNER JOIN ref_accounttypes ra ON ar.AccountTypeID = ra.AccountTypeID
                     INNER JOIN menus m ON ar.MenuID = m.MenuID
                     LEFT JOIN submenus sm ON ar.SubMenuID = sm.SubMenuID WHERE ar.AccountTypeID = ? ORDER BY ar.AccountTypeID ASC";
            $this->prepare($stmt);
            $this->bindparameter(1, $zaccounttypeID);
        }
        else
        {
            $stmt = "SELECT COUNT(*) as ctraccess FROM accessrights ar
                     INNER JOIN ref_accounttypes ra ON ar.AccountTypeID = ra.AccountTypeID
                     INNER JOIN menus m ON ar.MenuID = m.MenuID
                     LEFT JOIN submenus sm ON ar.SubMenuID = sm.SubMenuID ORDER BY ar.AccountTypeID ASC";
            $this->prepare($stmt);
        }
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * Menu Deactivation - Set the menu status to 0
     * @param int Menu ID
     * @return number of rows affected or 0
     */
    function deactivatemenu($zmenuID, $zstatus)
    {
        $this->begintrans();
        $this->prepare("UPDATE menus SET Status = ? WHERE MenuID = ?");
        $this->bindparameter(1, $zstatus);
        $this->bindparameter(2, $zmenuID);
        if($this->execute())
        {
            $updated = $this->rowCount();
            $this->committrans();
            return $updated;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    /**
     * Counts accounts table for jqgrid pagination
     */
    function countaccounts()
    {
        $stmt = "Select COUNT(*) ctracc from accounts as a 
                 INNER JOIN accountdetails as b ON a.AID = b.AID 
                 ORDER BY a.UserName ASC";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * For viewing of accounts single, jqgrid pagination
     *@param int account ID
     */
    function viewaccounts($zaid, $zstart, $zlimit, $zsort, $zdirection)
    {
        //if not jqgrid pagination 
        if($zaid > 0)
        {
            $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a 
                     INNER JOIN accountdetails as b ON a.AID = b.AID WHERE a.AID = ?";
            $this->prepare($stmt);
            $this->bindparameter(1, $zaid);
        }
        else
        {
            $stmt = "Select a.AID, a.Status, a.UserName,a.AccountTypeID, b.Name, b.Email, b.Address from accounts as a 
                 INNER JOIN accountdetails as b ON a.AID = b.AID 
                 ORDER BY ".$zsort." ".$zdirection." LIMIT ".$zstart.", ".$zlimit."";
            $this->prepare($stmt);
        }
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * Populates user accounts dropdown box
     */
    function viewuseraccounts()
    {
        $stmt = "SELECT AID, UserName FROM accounts ORDER BY UserName ASC";
        $this->prepare($stmt);
        $this->execute();
        return $this->fetchAllData();
    }
    
    /**
     * Deactivates a particular user, set Status to 4 (Locked (Admin))
     */
    function deactivateaccount($zaid)
    {
        $this->begintrans();
        $this->prepare("UPDATE accounts SET Status = 4 WHERE AID = ?");
        $this->bindparameter(1, $zaid);
        if($this->execute())
        {
            $this->committrans();
            return 1;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
    }
     
    /**
     * any changes made on submenus, menus and accessrights should be save on the xml
     * @param array account types 
     * @param array containing access rights data
     * @param string filename/path to xml file
     * @link http://www.php.net/manual/en/domdocument.createattribute.php
     */
    function writetoxml($zacctypes, $arraccess, $filename)
    {
        $ctr = 0;
        $doc = new DOMDocument('1.0', "UTF-8");
        $doc->formatOutput = true;
        
        $rights = $doc->createElement("rights");
        $doc->appendChild($rights);
        
        while($ctr < count($zacctypes))
        {
            $vAID = $zacctypes[$ctr]['AccountTypeID'];
            $ctr1 = 0;
            $vfirst = 1;
            while($ctr1 < count($arraccess))
            {
                $group = " ";
                //check if group id is not null
                if($arraccess[$ctr1]['Group'] <> null)
                {
                    $group = $arraccess[$ctr1]['Group'];
                }
                //group by Account Type ID
                if($vAID == $arraccess[$ctr1]['AccountTypeID'])
                {
                    if($vfirst == 1)
                    {
                        //right
                        $right = $doc->createElement("right");
                        $rightattrib = $doc->createAttribute('id');
                        $rightattrib->value = $arraccess[$ctr1]['AccountTypeID'];
                        $right->appendChild($rightattrib);

                        //menu
                        $menu = $doc->createElement("menu");
                        $attribid = $doc->createAttribute("id");
                        $attribname = $doc->createAttribute("name");
                        $attribpage = $doc->createAttribute("pagename");
                        $attribpath = $doc->createAttribute("path");

                        $attribid->value = $arraccess[$ctr1]['MenuID'];
                        $attribname->value = $arraccess[$ctr1]['MenuName'];
                        $attribpage->value = $arraccess[$ctr1]['MenuID'];
                        $attribpath->value = $arraccess[$ctr1]['MenuLink'];

                        $menu->appendChild($attribid);
                        $menu->appendChild($attribname);
                        $menu->appendChild($attribpage);
                        $menu->appendChild($attribpath);

                        //submenu
                        //validate if some menus do not have sub menus
                        if($arraccess[$ctr1]['SubMenuID'] <> null)
                        {
                            $submenu = $doc->createElement("submenu");
                            $subid = $doc->createAttribute("id");
                            $subname = $doc->createAttribute("name");
                            $suburl2 = $doc->createAttribute("path");
                            $subgroup = $doc->createAttribute("group");
                            $submenuid = $doc->createAttribute("pagename");

                            $subid->value = $arraccess[$ctr1]['SubMenuID'];
                            $subname->value = $arraccess[$ctr1]['SubMenuName'];
                            $suburl2->value = $arraccess[$ctr1]['DefaultURL2'];
                            $subgroup->value = $group;
                            $submenuid->value = $arraccess[$ctr1]['MenuID'];

                            $submenu->appendChild($subid);
                            $submenu->appendChild($subname);
                            $submenu->appendChild($suburl2);
                            $submenu->appendChild($subgroup);
                            $submenu->appendChild($submenuid);
                            $menu->appendChild($submenu);
                        }
                        
                        $right->appendChild($menu);
                        $rights->appendChild($right);
                        $vfirst = 2;
                        $menuID = $arraccess[$ctr1]['MenuID'];
                    }
                    else
                    {
                        //validate if submenus are having same menu ID
                        if($menuID == $arraccess[$ctr1]['MenuID'])
                        {
                            if($arraccess[$ctr1]['SubMenuID'] <> null)
                            {
                                $submenu = $doc->createElement("submenu");
                                $subid = $doc->createAttribute("id");
                                $subname = $doc->createAttribute("name");
                                $suburl2 = $doc->createAttribute("path");
                                $subgroup = $doc->createAttribute("group");
                                $submenuid = $doc->createAttribute("pagename");

                                $subid->value = $arraccess[$ctr1]['SubMenuID'];
                                $subname->value = $arraccess[$ctr1]['SubMenuName'];
                                $suburl2->value = $arraccess[$ctr1]['DefaultURL2'];
                                $subgroup->value = $group;
                                $submenuid->value = $arraccess[$ctr1]['MenuID'];

                                $submenu->appendChild($subid);
                                $submenu->appendChild($subname);
                                $submenu->appendChild($suburl2);
                                $submenu->appendChild($subgroup);
                                $submenu->appendChild($submenuid);
                                $menu->appendChild($submenu);
                            }
                            $right->appendChild($menu);
                            $rights->appendChild($right);
                            $menuID = $arraccess[$ctr1]['MenuID'];
                        }
                        else
                        {
                            //menus
                            $menu = $doc->createElement("menu");
                            $attribid = $doc->createAttribute("id");
                            $attribname = $doc->createAttribute("name");
                            $attribpage = $doc->createAttribute("pagename");
                            $attribpath = $doc->createAttribute("path");

                            $attribid->value = $arraccess[$ctr1]['MenuID'];
                            $attribname->value = $arraccess[$ctr1]['MenuName'];
                            $attribpage->value = $arraccess[$ctr1]['MenuID'];
                            $attribpath->value = $arraccess[$ctr1]['MenuLink'];

                            $menu->appendChild($attribid);
                            $menu->appendChild($attribname);
                            $menu->appendChild($attribpage);
                            $menu->appendChild($attribpath);

                            //submenus
                            //validate if some menus do not have sub menus
                            if($arraccess[$ctr1]['SubMenuID'] <> null)
                            {
                                $submenu = $doc->createElement("submenu");
                                $subid = $doc->createAttribute("id");
                                $subname = $doc->createAttribute("name");
                                $suburl2 = $doc->createAttribute("path");
                                $subgroup = $doc->createAttribute("group");
                                $submenuid = $doc->createAttribute("pagename");

                                $subid->value = $arraccess[$ctr1]['SubMenuID'];
                                $subname->value = $arraccess[$ctr1]['SubMenuName'];
                                $suburl2->value = $arraccess[$ctr1]['DefaultURL2'];
                                $subgroup->value = $group;
                                $submenuid->value = $arraccess[$ctr1]['MenuID'];

                                $submenu->appendChild($subid);
                                $submenu->appendChild($subname);
                                $submenu->appendChild($suburl2);
                                $submenu->appendChild($subgroup);
                                $submenu->appendChild($submenuid);
                                $menu->appendChild($submenu);
                            }
                            $right->appendChild($menu);
                            $rights->appendChild($right);
                            $menuID = $arraccess[$ctr1]['MenuID'];
                        }
                    }
                }
                $ctr1++;
            }
            $ctr++;
        }
        $doc->save($filename);
    }
    
    function getMaxAccessOrder($zmenuID, $zacctypeID){
        $stmt = "SELECT MAX(OrderID) AS ctrorder FROM accessrights WHERE MenuID = ? AND AccountTypeID = ?";
        $this->prepare($stmt);
        $this->bindparameter(1, $zmenuID);
        $this->bindparameter(2, $zacctypeID);
        $this->execute();
        return $this->fetchData();
    }
    
    /**
     * Sub-menu Deactivation - Set the submenu status to 0
     * @param int Menu ID
     * @param int Sub-menu ID
     * @return number of rows affected or 0
     */
    function deactivatesubmenu($zmenuID, $zsubmenuid, $zstatus)
    {
        $this->begintrans();
        $this->prepare("UPDATE submenus SET Status = ? WHERE MenuID = ? AND SubMenuID = ?");
        $this->bindparameter(1, $zstatus);
        $this->bindparameter(2, $zmenuID);
        $this->bindparameter(3, $zsubmenuid);
        if($this->execute())
        {
            $updated = $this->rowCount();
            $this->committrans();
            return $updated;
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
    }
    
    function deleteaccessrights($zrightID){
        $this->begintrans();
        try{
            $this->prepare("DELETE FROM accessrights WHERE ID = ?");
            $this->bindparameter(1, $zrightID);
            $this->execute();
            try{
                $this->committrans();
                return 1;
            }catch(PDOException $e){
                $this->rollbacktrans();
                return 0;
            }
        }catch(PDOException $e) {
            $this->rollbacktrans();
            return 0;
        }
        
    }
}

?>