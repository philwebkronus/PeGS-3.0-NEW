<?php

/**
 * @author owliber
 * @date Oct 22, 2012
 * @filename SiteMenuController.php
 * 
 */
class SubMenuController extends VMSBaseIdentity {

    public $updateDialog = false;
    public $deleteDialog = false;
    public $statusDialog = false;

    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
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
            $model = new SubMenu();
            $rawData = $model->getAllAvailableSubMenus();

            $arrayDataProvider = new CArrayDataProvider($rawData, array(
                'keyField' => 'SubMenuID',
                'sort' => array(
                    'attributes' => array('MenuID'),
                    'defaultOrder' => array('MenuID' => true),
                ),
                'pagination' => array(
                    'pageSize' => 10,
                ),
            ));

            $submenu = array();

            if (isset($_GET['MenuID'])) {

                $submenuid = $_GET['SubMenuID'];
                $submenu = $model->getSubMenuByID($submenuid);
            } else {
                $submenu = array('SubMenuID' => null, 'MenuID' => null, 'Name' => null, 'Link' => null, 'Description' => null, 'Status' => null);
            }

            if (isset($_POST['Submit'])) {

                switch ($_POST['Submit']) {
                    case 'Create':

                        $submenu['MenuID'] = $_POST['MenuID'];
                        $submenu['Submenu'] = trim($_POST['Submenu']);
                        $submenu['Link'] = trim($_POST['Link']);
                        $submenu['Description'] = trim($_POST['Description']);
                        $submenu['Status'] = $_POST['Status'];

                        $model->insertSubMenu($submenu);

                        //Log to audit trail            
                        $transDetails = ' ' . $_POST['Submenu'] . ' for menuid ' . $_POST['MenuID'];
                        AuditLog::logTransactions(6, $transDetails);

                        break;

                    case 'Update':

                        $submenu['SubMenuID'] = $_POST['SubMenuID'];
                        $submenu['MenuID'] = $_POST['MenuID'];
                        $submenu['Name'] = trim($_POST['Name']);
                        $submenu['Link'] = trim($_POST['Link']);
                        $submenu['Description'] = trim($_POST['Description']);
                        $submenu['Status'] = $_POST['Status'];

                        $model->updateSubMenuByID($submenu);

                        //Log to audit trail            
                        $transDetails = ' ID ' . $_POST['SubMenuID'];
                        AuditLog::logTransactions(7, $transDetails);

                        break;

                    case 'Delete':
                        $model->deleteSubMenuByID($_POST['SubMenuID']);

                        //Log to audit trail            
                        $transDetails = ' ID ' . $_POST['SubMenuID'];
                        AuditLog::logTransactions(8, $transDetails);

                        break;

                    case 'Enable':
                        $model->changeMenuStatusByID($_POST['SubMenuID'], $_POST['Status']);

                        //Log to audit trail            
                        $transDetails = ' ID ' . $_POST['SubMenuID'] . 'status to Enabled';
                        AuditLog::logTransactions(7, $transDetails);

                        break;
                    case 'Disable':
                        $model->changeMenuStatusByID($_POST['SubMenuID'], $_POST['Status']);

                        //Log to audit trail            
                        $transDetails = ' ID ' . $_POST['SubMenuID'] . 'status to Disabled';
                        AuditLog::logTransactions(7, $transDetails);

                        break;
                }

                $this->redirect("manage");
            }//Submit

            $this->render('submenus', array(
                'arrayDataProvider' => $arrayDataProvider,
                'submenu' => $submenu,
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
            $model = new SubMenu();
            $rawData = $model->getAllAvailableSubMenus();

            $arrayDataProvider = new CArrayDataProvider($rawData, array(
                'keyField' => 'SubMenuID',
                'sort' => array(
                    'attributes' => array('MenuID'),
                    'defaultOrder' => array('MenuID' => true),
                ),
                'pagination' => array(
                    'pageSize' => 10,
                ),
            ));

            $submenu = $model->getSubMenuByID($_GET['SubMenuID']);
            $this->updateDialog = true;

            $this->render('submenus', array(
                'arrayDataProvider' => $arrayDataProvider,
                'submenu' => $submenu,
            ));
        }
    }

    public function actionDelete() {
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
            $model = new SubMenu();
            $rawData = $model->getAllAvailableSubMenus();

            $arrayDataProvider = new CArrayDataProvider($rawData, array(
                'keyField' => 'SubMenuID',
                'sort' => array(
                    'attributes' => array('MenuID'),
                    'defaultOrder' => array('MenuID' => true),
                ),
                'pagination' => array(
                    'pageSize' => 10,
                ),
            ));

            $submenuid = $_GET['SubMenuID'];
            $submenu = SubMenu::getSubMenuByID($submenuid);
            $this->deleteDialog = true;

            $this->render('submenus', array(
                'arrayDataProvider' => $arrayDataProvider,
                'submenu' => $submenu,
            ));
        }
    }

    public function actionChangeStatus() {
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
            $model = new SubMenu();

            $rawData = $model->getAllAvailableSubMenus();

            $arrayDataProvider = new CArrayDataProvider($rawData, array(
                'keyField' => 'SubMenuID',
                'sort' => array(
                    'attributes' => array('MenuID'),
                    'defaultOrder' => array('MenuID' => true),
                ),
                'pagination' => array(
                    'pageSize' => 10,
                ),
            ));

            $this->statusDialog = true;

            $submenuid = $_GET['SubMenuID'];

            $submenu = $model->getSubMenuByID($submenuid);


            $this->render('submenus', array(
                'arrayDataProvider' => $arrayDataProvider,
                'submenu' => $submenu,
                'statusDialog' => $this->statusDialog,
            ));
        }
    }

}

?>
