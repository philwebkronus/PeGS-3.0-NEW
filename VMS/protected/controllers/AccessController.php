<?php

/**
 * @author owliber
 * @date Oct 30, 2012
 * @filename AccessRightsController.php
 * 
 */
class AccessController extends VMSBaseIdentity {

    public $layout = '//layouts/column2';

    public function actionManage() {
        $_AccountSessions = new SessionModel();

        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }

        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);

        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        } else {
            $menus = new SiteMenu();
            $accounttypes = new AccountTypes();
            $access = new AccessRights();

            $accountType = $accounttypes->accountTypes();

            if (!empty($_POST['AccountType'])) {
                $accounttypeid = $_POST['AccountType'];
            }
            else
                $accounttypeid = 1; //set the default value

            if (!empty($_POST['Submit'])) {
                $menuid = $_POST['MenuID'];

                $access->removeMenuRights($accounttypeid, $menuid);

                //Log to audit trail            
                $transDetails = ' menus (' . implode(',', $menuid) . ') for account group ' . $accounttypeid;
                AuditLog::logTransactions(11, $transDetails);

                foreach ($menuid as $menu) {
                    if (!$access->checkMenuAccess($accounttypeid, $menu))
                        $access->setAccessRightMenu($accounttypeid, $menu);

                    if (!empty($_POST['SubMenuID'])) {
                        $submenuid = $_POST['SubMenuID'];

                        $access->removeSubMenuRights($accounttypeid, $menuid, $submenuid);

                        foreach ($submenuid as $submenu) {
                            if (!$access->checkSubMenuAccess($accounttypeid, $submenu))
                                $access->setAccessRightSubMenu($accounttypeid, $submenu);
                        }
                    }
                }

                //Log to audit trail
                $transDetails = ' submenu (' . implode(',', $submenuid) . ') for account group ' . $accounttypeid;
                AuditLog::logTransactions(11, $transDetails);
            }

            $menuitems = $menus->getAllMenus();

            foreach ($menuitems as $row) {
                $menuid = $row['MenuID'];
                $submenuitems = SubMenu::getAllSubMenus($menuid);
            }

            $accessrights = $access->getAccessRights($accounttypeid);

            $this->render('index', array(
                'menus' => $menuitems,
                'submenus' => $submenuitems,
                'accountType' => $accountType,
                'accessrights' => $accessrights,
                'accounttypeid' => $accounttypeid,
            ));
        }
    }

    public function actionUpdate() {
        $_AccountSessions = new SessionModel();

        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }

        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);

        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        } else {
            if (!empty($_GET['menuid']) && !empty($_GET['accounttypeid'])) {
                $accounttypeid = $_GET['accounttypeid'];
                $menuid = $_GET['menuid'];
                AccessRights::setDefaultPage($accounttypeid, $menuid);

                //Log to audit trail
                $transDetails = ' default page to menuid ' . $menuid . ' for account group ' . $accounttypeid;
                AuditLog::logTransactions(11, $transDetails);

                $this->redirect("manage");
            }
        }
    }

}

?>
