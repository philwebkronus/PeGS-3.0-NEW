<div class="ui-widget ui-widget-content ui-corner-all" style=" width: 400px; margin: auto">
    <h1 class="ui-widget-header centerText">LOGIN</h1>
    <p>Please fill out the following form with your login credentials:</p>
    <div class="form wide">
    <?php if(Yii::app()->user->hasFlash('success')):?>
        <div class="ui-state-highlight ui-corner-all">
            <span class="ui-icon ui-icon-info" style="float: left; margin-right: 0.3em;"></span>
            <?php echo Yii::app()->user->getFlash('success'); ?>
        </div>
        <br />
    <?php endif; ?>
    <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'login-form',
            'action'=>CHtml::normalizeUrl(array('/managerss/auth/login')),
//            'enableClientValidation'=>true,
//            'clientOptions'=>array(
//                    'validateOnSubmit'=>true,
//            ),
            'method'=>'POST',
            'focus'=>array($model,'username'),
    )); ?>

            <p class="note">Fields with <span class="required">*</span> are required.</p>

            <div class="row">
                    <?php echo $form->labelEx($model,'username'); ?>
                    <?php echo $form->textField($model,'username'); ?>
                    <?php echo $form->error($model,'username'); ?>
            </div>

            <div class="row">
                    <?php echo $form->labelEx($model,'password'); ?>
                    <?php echo $form->passwordField($model,'password'); ?>
                    <?php echo $form->error($model,'password'); ?>
            </div>

            <div class="row buttons">
                    <?php //echo CHtml::submitButton('Login',array('class'=>'btnLogin')); ?>
                    <button class="btnLogin" href="<?php echo $this->createUrl('create') ?>">Login</button>
            </div>
    <?php $this->endWidget(); ?>
    </div><!-- form -->
</div>
<div class="clear"></div>
<script type="text/javascript">
$(document).ready(function(){
    $('.btnLogin').button({icons: {primary: 'ui-icon-unlocked'}});
})
</script>