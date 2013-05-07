<?php

/*
 * @Date Feb 1, 2013
 * @Author owliber
 * 
 */
?>

<?php

class UsageController extends VMSBaseIdentity
{
    public $dateFrom;
    public $dateTo;
    public $status;
    public $vouchertype = 'All';
    public $egmmachine = 'All';
    public $site = 'All';
    
    public function actionIndex()
    {
        $this->dateFrom = date('Y-m-d');
        $this->dateTo = date('Y-m-d',strtotime ('+1 day' , strtotime($this->dateFrom)));
        
        $totalAmount = 0;
        $totalCount = 0;
            
        if($this->egmmachine == 'All')
        {
            if(Yii::app()->user->isPerSite())
            {
                $this->site = Yii::app()->user->getSiteID();
                $total = $model->getSummaryBySite($this->site);

                foreach($total as $row)
                {
                    $totalCount = $row['TotalCount'];
                    $totalAmount = $row['TotalAmount'];
                }
                
                $egmmachines = Stacker::activeEGMMachinesBySite($this->site);
                
            }
            else
            {
                $egmmachines = Stacker::activeEGMMachines();
            }
                        

            foreach($egmmachines as $value)
            {
                $egmmachine[] = $value['EGMMachineInfoId_PK'];
            }

            $egmmachines = $egmmachine;

        }
        else
            $egmmachines = $this->egmmachine;
            
        $model = new Usage();

        $voucherusage = $model->getUsage($this->dateFrom, $this->dateTo, $this->vouchertype, $this->status, $egmmachines);

        $dataProvider = new CArrayDataProvider($voucherusage, array(
            'keyField'=>'VoucherID',
            'pagination'=>array(
                'pageSize'=>25,
            ),
        ));
        
        $this->render('index',array(
            'totalAmount'=>$totalAmount,
            'totalCount'=>$totalCount,
            'dataProvider'=>$dataProvider,
        ));
    }
    
    public function actionAjaxVoucherUsage()
    {
        if(Yii::app()->request->isAjaxRequest)
        {
            $this->dateFrom = $_GET['DateFrom'];
            $this->dateTo = $_GET['DateTo'];
            
            if(Yii::app()->user->isPerSite())
                $this->site = Yii::app()->user->getSiteID();
            else
                $this->site = $_GET['Site'];
            
            if($this->egmmachine == 'All')
            {
                $egmmachines = Stacker::activeEGMMachinesBySite($this->site);

                foreach($egmmachines as $value)
                {
                    $egmmachine[] = $value['EGMMachineInfoId_PK'];
                }

                $egmmachines = $egmmachine;

            }
            else
                $egmmachines = $this->egmmachine;
        
            $model = new Usage();
            
            $voucherusage = $model->getUsage($this->dateFrom, $this->dateTo, $this->vouchertype, $this->status, $egmmachines);

            $dataProvider = new CArrayDataProvider($voucherusage, array(
                'keyField'=>'VoucherID',
                'pagination'=>array(
                    'pageSize'=>25,
                ),
            ));

            $this->renderPartial('_lists',array(
                'dataProvider'=>$dataProvider,
            ));

            Yii::app()->end();
        }
    }
}
?>