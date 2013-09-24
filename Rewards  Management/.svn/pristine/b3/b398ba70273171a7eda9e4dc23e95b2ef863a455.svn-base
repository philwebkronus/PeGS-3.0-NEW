<?php
/* @var $this ManagePartnersController */
/* @var $model ManagePartners */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'manage-partners-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'PartnerID'); ?>
		<?php echo $form->textField($model,'PartnerID'); ?>
		<?php echo $form->error($model,'PartnerID'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'CompanyAddress'); ?>
		<?php echo $form->textField($model,'CompanyAddress',array('size'=>60,'maxlength'=>150)); ?>
		<?php echo $form->error($model,'CompanyAddress'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'CompanyEmail'); ?>
		<?php echo $form->textField($model,'CompanyEmail',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'CompanyEmail'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'CompanyPhone'); ?>
		<?php echo $form->textField($model,'CompanyPhone',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'CompanyPhone'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'CompanyFax'); ?>
		<?php echo $form->textField($model,'CompanyFax',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'CompanyFax'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'CompanyWebsite'); ?>
		<?php echo $form->textField($model,'CompanyWebsite',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'CompanyWebsite'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ContactPerson'); ?>
		<?php echo $form->textField($model,'ContactPerson',array('size'=>60,'maxlength'=>150)); ?>
		<?php echo $form->error($model,'ContactPerson'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ContactPersonPosition'); ?>
		<?php echo $form->textField($model,'ContactPersonPosition',array('size'=>60,'maxlength'=>150)); ?>
		<?php echo $form->error($model,'ContactPersonPosition'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ContactPersonPhone'); ?>
		<?php echo $form->textField($model,'ContactPersonPhone',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'ContactPersonPhone'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ContactPersonMobile'); ?>
		<?php echo $form->textField($model,'ContactPersonMobile',array('size'=>20,'maxlength'=>20)); ?>
		<?php echo $form->error($model,'ContactPersonMobile'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'ContactPersonEmail'); ?>
		<?php echo $form->textField($model,'ContactPersonEmail',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'ContactPersonEmail'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'NumberOfRewardOffers'); ?>
		<?php echo $form->textField($model,'NumberOfRewardOffers'); ?>
		<?php echo $form->error($model,'NumberOfRewardOffers'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'Option1'); ?>
		<?php echo $form->textField($model,'Option1',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'Option1'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'Option2'); ?>
		<?php echo $form->textField($model,'Option2',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'Option2'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'Option3'); ?>
		<?php echo $form->textField($model,'Option3',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'Option3'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->