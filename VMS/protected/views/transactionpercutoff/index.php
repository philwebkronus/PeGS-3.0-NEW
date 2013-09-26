<?php
$this->breadcrumbs=array(
	'Transaction Per Cut-off',
);?>
<?php
$siteconversionmodel = new SiteConversionForm;
if(isset($_POST['TransactionpercutoffForm']))
{
    $model->attributes=$_POST['TransactionpercutoffForm'];
}
else
{
    if(isset($_GET['page']))
    {
        $model->transactiondate=substr(Yii::app()->session['transactiondate'], 0, 10); 
        $model->site=Yii::app()->session['site'];
    }
    else
    {
        $model->transactiondate = date('Y-m-d');

    }
}
?>
<h2>Transaction Per Cut-off</h2>
<hr color="black" />
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
<?php $form=$this->beginWidget('CActiveForm', array(
    'enableClientValidation'=>true,
    'clientOptions'=>array(
    'validateOnSubmit'=>true,
    ),
)); ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width:500px">
        <tr>
            <td><?php echo $form->labelEx($model,'Transaction Date : ');?></td>    
            <td>
                
                <?php echo $form->textField($model,'transactiondate', array('id'=>'transactiondate','readonly'=>'true', /*'value'=>date('Y-m-d'),*/ 'style'=>'width: 120px;')).
                      CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton","class"=>"pointer","style"=>"cursor: pointer;"));
                      $this->widget('application.extensions.calendar.SCalendar',
                      array(
                      'inputField'=>'transactiondate',
                      'button'=>'calbutton',
                      'showsTime'=>false,
                      'ifFormat'=>'%Y-%m-%d',
                      ));                
                ?>
            </td>
        </tr> 
        <tr>
        <td><?php echo CHtml::label("eGames : ", "site");?></td>    
        <td>
                <?php echo $form->dropDownList($model, 'site', $siteconversionmodel->getSiteName(), array('id'=>'site')); ?>
        </td>
        </tr>
        <tr>
        <td><?php echo CHtml::label("Voucher Type : ", "vouchertype");?></td>    
        <td>
                <?php echo $form->dropDownList($model, 'vouchertype',  array('All' => 'All', '1' => 'Ticket', '2' => 'Coupon'), array('id'=>'vouchertype')); ?>
        </td>
        </tr>
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
            <?php echo CHtml::submitButton("Submit"); ?>
    </div> 
</div>
<div>
    <?php
            $this->actionTransactionPerCutOffDataTable(Yii::app()->session['rawData']);       
    ?>
</div>
<?php $this->endWidget(); ?>
<?php //$this->renderPartial('siteconversion', array('arrayDataProvider'=>$arrayDataProvider)) ?>