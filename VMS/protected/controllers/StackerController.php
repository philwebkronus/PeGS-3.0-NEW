<?php

/*
 * @Date Nov 20, 2012
 * @Author owliber
 */
?>
<?php
class StackerController extends VMSBaseIdentity
{
    public $dateFrom;
    public $dateTo;
    public $egmmachine = 'All';
    public $site = 'All';
    public $stackersession = false; //0 - Unended; 1 - Ended
    public $advanceFilter = false;
    public $isAdmin = false;
    public $isAdvance;
    public $isEnded;
        
    public function actionMonitor()
    {
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
        }
        else{
        //Log access to audit trail
        AuditLog::logTransactions(18);
        
        // Unset user sessions if there are any
        unset(Yii::app()->session['isAdvance']);
        unset(Yii::app()->session['isEnded']);
        unset(Yii::app()->session['DateTo']);
        unset(Yii::app()->session['DateFrom']);
        unset(Yii::app()->session['EGM']);
        $display = 'none';
        Yii::app()->session['display'] = $display;
        
        $model = new Stacker();
        
        $this->dateFrom = date('Y-m-d');
        $this->dateTo = $this->dateFrom;
        
        if(Yii::app()->request->isAjaxRequest)
            $siteID = $this->site;
        else
            $siteID = Yii::app()->user->getSiteID();

        if($this->egmmachine == 'All')
        {
            if(Yii::app()->user->isPerSite())
                $egmmachines = Stacker::activeEGMMachinesBySite($siteID);    
            else
                $egmmachines = Stacker::activeEGMMachines();

            foreach($egmmachines as $value)
            {
                $egmmachine[] = $value['EGMMachineInfoId_PK'];
            }

            $egmmachines = $egmmachine;

        }
        else
            $egmmachines = $this->egmmachine;
                
        $stackerlogs = $model->getStackerSessions($egmmachines);
        
        $dataProvider = new CArrayDataProvider($stackerlogs, array(
            'keyField'=>'EGMStackerSessionID',
            'pagination'=>array(
                'pageSize'=>10,
            ),
        ));
        
        $this->render('index',array(
            'dataProvider'=>$dataProvider,
        ));
        }
    }
    
    public function actionAjaxStackerSessions()
    {
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
        }
        else{
        if(Yii::app()->request->isAjaxRequest)
        {
            if(isset( Yii::app()->session['isEnded']))
                 unset( Yii::app()->session['isEnded']);
            
            if($_GET['Site'] == "empty" && $_GET['EGM'] == "empty")
            {
                echo ('Please select a site and EGM then try again.');
                Yii::app()->end();
            }    
            //Selected account groups has view to Site lists
            if(isset($_GET['Site']) && $_GET['Site'] != 'undefined')
                $this->isAdmin = true;
            
            if(isset($_GET['IsAdvance']) && $_GET['IsAdvance'] == 1)//true
            {
                if($_GET['DateFrom'] > date('Y-m-d'))
                {
                    echo ('Date must not be greater than today.');
                    Yii::app()->end();
                }
                elseif($_GET['DateTo'] > date('Y-m-d')){
                    echo ('Date must not be greater than today.');
                    Yii::app()->end();
                }
            
                $this->dateFrom = $_GET['DateFrom'];
                $this->dateTo = $_GET['DateTo'];
                $this->advanceFilter = true;
                
                Yii::app()->session['isAdvance'] = 1;
                Yii::app()->session['DateFrom'] = $this->dateFrom;
                Yii::app()->session['DateTo'] = $this->dateTo;
                Yii::app()->session['Site'] = $_GET['Site'];
                
                if($_GET['StackerSession'] == 1)
                {
                    $this->stackersession = true;;
                    Yii::app()->session['isEnded'] = 1;
                }
                else
                    $this->stackersession = false;
                
            }
            else
            {
                $this->advanceFilter = false;
                
            }
            
            if($this->isAdmin)
                $this->site = $_GET['Site'];
            else
                $this->site = Yii::app()->user->getSiteID();
            
            //EGM machines
            $this->egmmachine = $_GET['EGM'];
            
            if($this->egmmachine == 'All')
            {
                $egmmachines = Stacker::activeEGMMachinesBySite($this->site);    

                if(empty($egmmachines)){
                    $egmmachines = NULL;
                }
                else{
                    foreach($egmmachines as $value)
                    {
                        $egmmachine[] = $value['EGMMachineInfoId_PK'];
                    }

                    $egmmachines = $egmmachine;
                }
                   
            }
            else{
                $egmmachines = $this->egmmachine;
            }
            
            if($egmmachines == NULL){
                    $egmmachines = 0;
                }
            
            Yii::app()->session['EGM'] = $this->egmmachine;
            
            $model = new Stacker();
            
            if($this->advanceFilter)
                $stackerlogs = $model->getAllStackerSessions($this->dateFrom,  $this->dateTo, $egmmachines, $this->stackersession);
            else
                $stackerlogs = $model->getStackerSessions($egmmachines);
           
            
            $dataProvider = new CArrayDataProvider($stackerlogs, array(
                'keyField'=>'EGMStackerSessionID',
                'id'=>'stacker-id'.uniqid(),
            ));

            $this->renderPartial('_lists',array(
                'dataProvider'=>$dataProvider,
            ));
            
            Yii::app()->end();
        }
        }
    }

    public function actionAjaxStackerDetails()
    {
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
        }
        else{
        if(Yii::app()->request->isAjaxRequest)
        {
           $model = new Stacker;
           $sessionID = $_GET['SessionID'];
           $egmmachine = $model->getMachineNameBySession($sessionID);
           
           $stackerentries = $model->getStackerEntriesBySessionID($sessionID);
           $totals = $model->getTotals($sessionID);
                      
           $dataProvider = new CArrayDataProvider($stackerentries, array(
                'keyField'=>'EGMStackerEntryID',
                'pagination'=>array(
                    'pageSize'=>10,
                ),
            ));
           
           $this->renderPartial('_details',array(
                'dataProvider'=>$dataProvider,
                'egmmachine'=>$egmmachine,
                'totals'=>$totals,
           ));
           
           Yii::app()->end();
                      
        }
        }
        
        
    } 
        
    public function actionAjaxEGMachines()
    {
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
        }
        else{
        if(Yii::app()->request->IsAjaxRequest)
        {
            $siteid = $_GET['SiteID'];
            
            $model = new Stacker();
            
            if($siteid == 'empty')
            {
                echo CHtml::tag('option',
                              array('value'=>'empty'),CHtml::encode("Select a machine"),true);
            }
            else
            {
                $data = $model->listActiveEGMMachines($siteid);

                foreach($data as $value=>$name)
                {
                    echo CHtml::tag('option',
                              array('value'=>$value),CHtml::encode($name),true);
                }
            }
            
            
            
        }
        }
        $display = 'block';
        Yii::app()->session['display'] = $display;
    }
}
?>
