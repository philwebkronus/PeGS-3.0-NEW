<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->layout = '/layouts/updatepassword';


$this->pageTitle=Yii::app()->name . ' - Update Password';

?>
<script type="text/javascript">
    function numberandletter(evt)
    {
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if (charCode == 96 || charCode == 60 || charCode == 62 || charCode == 44 || charCode == 59 || charCode == 34)
      {
          return false;
      }
      else if (charCode > 31 && (charCode < 33 || charCode > 38) && (charCode < 42 || charCode > 63) && (charCode < 95 || charCode > 122)){
          return false;
      }
      else if(charCode == 9)
      {
          return true;
      }
      else
          return true;
    }
</script>    
<div class="form updatepassword">
    <div class="updatepassword-title"><?php echo "Update Password" ?></div>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>


	<div class="row">
		<?php echo $form->labelEx($model,'Username'); ?>
		<?php echo $form->textField($model,'Username',array('readonly' => 'readonly', 'value' => $this->Username)) ?>
		<?php echo $form->error($model,'Username'); ?>
	</div>
        <div class="row">
		<?php echo $form->labelEx($model,'TempPass'); ?>
		<?php echo $form->passwordField($model,'TempPass',array('value' => $this->TempPass, 'readonly' => 'readonly','maxlength' => 30)); ?>
		<?php echo $form->error($model,'TempPass'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'NewPassword'); ?>
		<?php echo $form->passwordField($model,'NewPassword',array('onkeypress' => 'return numberandletter(event);', 'maxlength' => 30)); ?>
		<?php echo $form->error($model,'NewPassword'); ?>
	</div>
        <div class="row">
		<?php echo $form->labelEx($model,'ConfirmPassword'); ?>
		<?php echo $form->passwordField($model,'ConfirmPassword',array('onkeypress' => 'return numberandletter(event);', 'maxlength' => 30)); ?>
		<?php echo $form->error($model,'ConfirmPassword'); ?>
	</div>
	<div class="row buttons">
		<?php echo CHtml::submitButton('Update Password'); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'error-message',
    'options'=>array(
        'title' => $this->dialogtitle,
        'autoOpen'=>$this->dialogshow,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'buttons' => array
        (
            'OK'=>'js:function(){$(this).dialog("close");}',
        ),
    ),
));
echo "<p style='text-align: left;'>";
echo $this->dialogmsg;
echo "<br/>";
echo "</p>";

$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'success-message',
    'options'=>array(
        'title' => $this->dialogtitle,
        'autoOpen'=>$this->dialogshow2,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'buttons' => array
        (
            'OK'=>'js:function(){
                        window.location.href = "../../";
                   }',
        ),
    ),
));
echo "<p style='text-align: left;'>";
echo $this->dialogmsg;
echo "<br/>";
echo "</p>";

$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
