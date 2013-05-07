<div class="ui-widget ui-widget-content ui-corner-all" style=" width: 400px; margin: auto">
    <h1 class="ui-widget-header centerText">CHANGE PASSWORD</h1>
    <div class="form wide prepend-1 append-1">
        <p>Your password has been expired. Please fill out the following form to update your credentials:</p>
    <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'login-form',
            'action'=>CHtml::normalizeUrl(array('/managerss/auth/changepassword')),
            'enableClientValidation'=>true,
            'clientOptions'=>array(
                    'validateOnSubmit'=>true,
            ),
            'method'=>'POST',
            'focus'=>array($model,'username'),
    )); ?>

            <p class="note">Fields with <span class="required">*</span> are required.</p>

            <div class="row">
                <?php echo $form->labelEx($model,'username'); ?>
                <?php echo $form->textField($model,'username',array('readonly'=>'readonly')); ?>
                <?php echo $form->error($model,'username'); ?>
            </div>

            <div class="row">
                <?php echo $form->labelEx($model,'oldpassword'); ?>
                <?php echo $form->passwordField($model,'oldpassword',array('readonly'=>'readonly')); ?>
                <?php echo $form->error($model,'oldpassword'); ?>
            </div>
            
            <div class="row">
                <?php echo $form->labelEx($model,'newpassword'); ?>
                <?php echo $form->passwordField($model,'newpassword'); ?>
                <?php echo $form->error($model,'newpassword'); ?>
            </div>
            
            <div class="row">
                <?php echo $form->labelEx($model,'confirmpassword'); ?>
                <?php echo $form->passwordField($model,'confirmpassword'); ?>
                <?php echo $form->error($model,'confirmpassword'); ?>
            </div>            

            <div class="row buttons">
                <button class="btnLogin" href="<?php echo $this->createUrl('create') ?>">Change Password</button>
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
