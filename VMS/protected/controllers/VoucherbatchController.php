    <?php

/**
 * @author owliber
 * @date Nov 6, 2012
 * @filename GenerateController.php
 * 
 */

class VoucherbatchController extends VMSBaseIdentity
{
    public $voucherType = 2;
    public $status = 'All';
    public $dateFrom;
    public $dateTo;
//    public $toggle_search = true;
    public $activateDialog = false;
    public $generateDialog = false;
    public $messageDialog = false;
    public $changeStatus = 1;
//    public $viewByBatch = false;
        
    //public $layout='//layouts/column2';
    
    public function actionManage()
    {
        $this->dateFrom = date('Y-m-d');
        $this->dateTo = date('Y-m-d');
                
        $model = new VoucherGeneration();
        
        $rawData = $model->getAllGeneratedVoucherBatch();
        
        $dataProvider = new CArrayDataProvider($rawData, array(
            'keyField'=>'VoucherBatchID',
            'sort'=>array(
                'attributes'=>array('BatchNumber','Quantity','Amount','DateGenerated','DateExpiry'),
                'defaultOrder'=>array('BatchNumber'=>true),
             ),
            'pagination'=>array(
                'pageSize'=>10,
            ),
        ));
        
//        if(Yii::app()->request->IsAjaxRequest)
//        {
//            unset(Yii::app()->session['ajax']);
//            unset(Yii::app()->session['batch-id_page']);
//            
//            Yii::app()->session['ajax'] = $_GET['ajax'];
//            Yii::app()->session['batch-id_page'] = $_GET['batch-id_page'];
//            
//        }
        
        $this->render('index',array(
            'dataProvider'=>$dataProvider,
        ));
    }
    
    public function actionGenerate()
    {
                
        if(isset($_POST['Quantity']) && is_numeric($_POST['Quantity'])
             && isset($_POST['Amount']) && is_numeric($_POST['Amount'])
             && isset($_POST['Validity']) && is_numeric($_POST['Validity']))
        {
            $quantity = $_POST['Quantity'];
            $amount = $_POST['Amount'];
            $validity = $_POST['Validity'];
        
            $AID = Yii::app()->session['AID'];

            $model = new VoucherGeneration;
            $result = $model->generateVoucherBatch($quantity, $amount, $validity, $AID);

            if($result['TransCode'] == 0)
            {
                $this->generateDialog = true;
                
                //Log to audit trail            
                $transDetails = ' # '.$result['BatchNumber'] . ' - Qty:'.$_POST['Quantity'].' Amt:'.$_POST['Amount'];
                AuditLog::logTransactions(12, $transDetails);

            }
            $this->actionManage();
        }
        else
            $this->redirect("manage");
          
    }
    
    public function actionList()
    {
            
        if(Yii::app()->request->IsAjaxRequest)
        {            
            $model = new VoucherGeneration();
           
             if(isset($_POST['DateFrom']) && isset($_POST['DateTo']) && isset($_POST['Status']))
             {
                $this->dateFrom = $_POST['DateFrom'];
                $this->dateTo = $_POST['DateTo'];
                $this->status = $_POST['Status'];  
                
                //+ 1 day to get all records for the selected day up to 23:59:59
                $newdate = strtotime ( '+1 day' , strtotime ( $this->dateTo ) ) ;
                $newDateTo = date ( 'Y-m-d' , $newdate );
                
                 //Put values into session, fix to paging state
                Yii::app()->session['dateFrom'] = $this->dateFrom;
                Yii::app()->session['dateTo'] = $this->dateTo;
                Yii::app()->session['status'] = $this->status;
                
             }
             else
             {
                if(isset(Yii::app()->session['dateFrom']) 
                        && isset(Yii::app()->session['dateTo'])
                        && isset(Yii::app()->session['status']))
                {
                   $this->dateFrom = date('Y-m-d', strtotime(Yii::app()->session['dateFrom']));
                    $this->dateTo = date('Y-m-d', strtotime(Yii::app()->session['dateTo']));
                    $this->status = Yii::app()->session['status'];
                    $newDateTo = $this->dateTo; 
                }
                else
                {
                    $this->dateFrom = date('Y-m-d');
                    $this->dateTo = date('Y-m-d');
                    $newDateTo = date('Y-m-d',strtotime('+1 DAYS'));
                }

             }
             
            if($this->status === 'All')
                $generatedvouchers = $model->getAllGeneratedVouchersByRange($this->dateFrom,$newDateTo);
            else 
                $generatedvouchers = $model->getAllGeneratedVouchersByStatus($this->dateFrom,$newDateTo,$this->status);

                
            if(isset($_GET['BatchNo']))
            {
                unset(Yii::app()->session['BatchNo']);
                
                $batchno = $_GET['BatchNo'];
                $generatedvouchers = $model->getAllGeneratedVouchers($batchno);
                Yii::app()->session['BatchNo'] = $batchno;
                
            }
            
            $dataProvider = new CArrayDataProvider($generatedvouchers, array(
                'keyField'=>'VoucherID',
                'sort'=>array(
                    'attributes'=>array('Amount','DateCreated','DateExpiry'),
                    'defaultOrder'=>array('DateCreated'=>true),
                 ),
                'pagination'=>array(
                    'pageSize'=>15,
                ),
            ));
            
            $this->renderPartial('_lists',array(
                'dataProvider'=>$dataProvider,
                
            ));
        }
        
    }
    
    public function actionChangeStatus()
    {
        $model = new VoucherGeneration();
        
        $rawData = $model->getAllGeneratedVoucherBatch();
        
        $dataProvider = new CArrayDataProvider($rawData, array(
            'keyField'=>'VoucherBatchID',
            'sort'=>array(
                'attributes'=>array('BatchNumber','Quantity','Amount','DateGenerated','DateExpiry'),
                'defaultOrder'=>array('BatchNumber'=>true),
             ),
            'pagination'=>array(
                'pageSize'=>15,
            ),
        ));
        
        if(Yii::app()->request->IsAjaxRequest)
        {
            $voucherinfo = $model->getAllGeneratedVoucherBatchByBatchNo($_GET['BatchNo']);
            $this->changeStatus = $_GET['status'];
            
            echo CJSON::encode(array(
                'activate'=>$_GET['status'],
                'batchno'=>$_GET['BatchNo'],
                'qty'=>$voucherinfo['Quantity'],
                'amount'=>$voucherinfo['Amount'],
            ));
                        
            exit;
        }
        
        if(isset($_POST['Submit']))
        {
            if($_POST['Submit'] == 'Activate')
            {
                $result = $model->activateVoucherBatch($_POST['BatchNo']);
                
                //Log to audit trail            
                $transDetails = ' # '.$_POST['BatchNo'];
                AuditLog::logTransactions(13, $transDetails);
                
                $this->changeStatus = 0;
            }
            else
            {
                $result = $model->deActivateVoucherBatch($_POST['BatchNo']);
                
                //Log to audit trail            
                $transDetails = ' # '.$_POST['BatchNo'];
                AuditLog::logTransactions(14, $transDetails);
                
                $this->changeStatus = 1;
            }
            
            if($result == 1)
            {                
                $this->messageDialog = true;
            }
            
            $voucherinfo = $model->getAllGeneratedVoucherBatchByBatchNo($_POST['BatchNo']);
        }        
        
        $this->render('index',array(
            'dataProvider'=>$dataProvider,
            'voucherinfo'=>$voucherinfo,
            
        ));
        
               
        
    }
    
    public function actionExportToCSV()
    {
        
        Yii::import('ext.ECSVExport');

        $model = new VoucherGeneration();

        if(isset($_GET['BatchNo']))
        {
            $batchno = $_GET['BatchNo'];
        }

        $rawData = $model->exportVoucherBatchByBatchNo($batchno);

        //Log to audit trail            
        $transDetails = ' # '.$batchno;
        AuditLog::logTransactions(15, $transDetails);

        $filename = 'VoucherBatch_'.$batchno.'_'.date('Y-m-d').'.csv';
        $csv = new ECSVExport($rawData);
        $csv->toCSV($filename); // returns string by default
        $content = file_get_contents($filename);

        Yii::app()->getRequest()->sendFile($filename, $content, "text/csv", false);
        
        unlink($filename);
        
        exit();
        
    }
    
}
?>
