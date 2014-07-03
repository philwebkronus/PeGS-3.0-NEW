<div style="margin-left: 20px;font-size: 14px;">
<!--    <br />
    <?php //echo CHtml::link('Verify', 'verify', array('target'=>'_blank')); ?>
    <br />
    <br />
    <?php //echo CHtml::link('Use', 'use', array('target'=>'_blank')); ?>
    <br />-->
    <br />
    <?php echo CHtml::link('VerifyTicket', Yii::app()->createUrl('wsVapiInvoker/verifyTicket'), array('target'=>'_blank')); ?>
    <br />
    <br />
    <?php echo CHtml::link('AddTicket', Yii::app()->createUrl('wsVapiInvoker/addTicket'), array('target'=>'_blank')); ?>
    To be consumed in KAPI: RedeemSession and StAPI: CancelDeposit
    <br />
    <br />
    <?php echo CHtml::link('UseTicket', Yii::app()->createUrl('wsVapiInvoker/useTicket'), array('target'=>'_blank')); ?>
    To be consumed only in StAPI: LogStackerTransaction
    <br />
    <br />
</div>