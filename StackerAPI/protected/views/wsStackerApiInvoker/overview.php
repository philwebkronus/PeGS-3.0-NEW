<div style="margin-left: 20px;font-size: 14px;">
    <br />
    <br />
    <?php echo CHtml::link('LogStackerSession', Yii::app()->createUrl('wsStackerApiInvoker/logstackersession'), array('target'=>'_blank')); ?>
    <br />
    <br />
    <?php echo CHtml::link('GetStackerBatchID',  Yii::app()->createUrl('wsStackerApiInvoker/getstackerbatchid'), array('target'=>'_blank')); ?>
    <br />
    <br />
    <?php echo CHtml::link('LogStackerTransaction', Yii::app()->createUrl('wsStackerApiInvoker/logstackertransaction'), array('target'=>'_blank')); ?>
    <br />
    <br />
    <?php echo CHtml::link('VerifyLogStackerTransaction', Yii::app()->createUrl('wsStackerApiInvoker/verifylogstackertransaction'), array('target'=>'_blank')); ?>
    <br />
    <br />
    <?php echo CHtml::link('AddStackerInfo', Yii::app()->createUrl('wsStackerApiInvoker/addstackerinfo'), array('target'=>'_blank')); ?>
    <br />
    <br />
    <?php echo CHtml::link('GetStackerInfo', Yii::app()->createUrl('wsStackerApiInvoker/getstackerinfo'), array('target'=>'_blank')); ?>
    <br />
    <br />
    <?php echo CHtml::link('CancelDeposit', Yii::app()->createUrl('wsStackerApiInvoker/canceldeposit'), array('target'=>'_blank')); ?>
    <br />
    <br />
</div>