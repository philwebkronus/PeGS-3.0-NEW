<?php

/**
 * @author owliber
 * @date Oct 22, 2012
 * @filename SiteMenuController.php
 * 
 */

class MenuController extends VMSBaseIdentity
{
    public $updateDialog = false;
    public $deleteDialog = false;
    public $statusDialog = false;
    
    /**
     * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
     * using two-column layout. See 'protected/views/layouts/column2.php'.
     */
    
    public $layout='//layouts/column2';
    
    public function actionManage()
    {
        $model = new SiteMenu();
        $rawData = $model->getAllAvailableMenus();
        
        $arrayDataProvider = new CArrayDataProvider($rawData, array(
            'keyField'=>'MenuID',
            'id'=>'menu-id',
            'sort'=>array(
                'attributes'=>array('MenuID'),
                'defaultOrder'=>array('MenuID'=>false),
             ),
            'pagination'=>array(
                'pageSize'=>15,
            ),
        ));
        
        $menu = array();
        
        if(!empty($_GET['MenuID']))
        {
            
            $menuid = $_GET['MenuID'];
            $menu = $model->getMenuByID($menuid);
                    
        }
        else
        {
            $menu = array('MenuID'=>null,'Name'=>null,'Link'=>null,'Description'=>null,'Status'=>null);
            
        }
        
        $result = array();
        
       if(isset($_POST['Submit']))
       {
           switch($_POST['Submit'])
           {
               case 'Create':
                   $menu['Name'] = trim($_POST['Name']);
                   $menu['Link'] = trim($_POST['Link']);
                   $menu['Description'] = trim($_POST['Description']);
                   $menu['Status'] = $_POST['Status'];
                   
                   $model->insertMenu($menu); 
                   
                   //Log to audit trail            
                   $transDetails = ' '.$_POST['Name'];
                   AuditLog::logTransactions(3, $transDetails);
            
                   break;
               case 'Update':
                   $menu["MenuID"] = $_POST['MenuID'];    
                   $menu['Name'] = trim($_POST['Name']);
                   $menu['Link'] = trim($_POST['Link']);
                   $menu['Description'] = trim($_POST['Description']);
                   $menu['Status'] = $_POST['Status'];
                   
                   $model->updateMenuByID($menu);                
                   $this->updateDialog = false; 
                   
                   //Log to audit trail            
                   $transDetails = ' ID '.$_POST['MenuID'];
                   AuditLog::logTransactions(4, $transDetails);
                   
                   break;
               case 'Delete':   
                   $model->deleteMenuByID($_POST['MenuID']);
                   
                   //Log to audit trail            
                   $transDetails = ' ID '.$_POST['MenuID'];
                   AuditLog::logTransactions(5, $transDetails);
                   
                   break;
               default:
                   $status = $_POST['Status'];
                   $model->changeMenuStatusByID($_POST['MenuID'],$status);
                   
                   //Log to audit trail            
                   $transDetails = ' ID '.$_POST['MenuID'].' status to '.$_POST['Status'];
                   AuditLog::logTransactions(4, $transDetails);
                   
                   break;
                  
           }
                      
           $this->redirect("manage");
           
       }//Submit
        
        $this->render('menus',array(
            'arrayDataProvider'=>$arrayDataProvider,
            'menu'=>$menu,
            'model'=>$model,
            'result'=>$result,
        ));
        
    }
        
    public function actionUpdate()
    {   
        
        if(isset($_GET['MenuID']))
        {
            $this->updateDialog = true;
            $this->actionManage();
                    
        }
        
    }
    
    public function actionDelete()
    {
        if(isset($_GET['MenuID']))
        {
            $this->deleteDialog = true;
            $this->actionManage();
        }
        
    }
    
    public function actionChangeStatus()
    {
        if(isset($_GET['MenuID']))
        {
            $menuid = $_GET['MenuID'];
            $menu = SiteMenu::getMenuByID($menuid);
            $this->statusDialog = true;
            $this->actionManage();
        }
    }
    
    public function getMenuIDByLink($link)
    {
        $query = "SELECT MenuID FROM menus WHERE Link =:link";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":link", $link);
        $result = $sql->queryRow();
        
        if(count($result)>0)
        {
            return $result['MenuID'];
        }
    }
      
}
?>
