<?php
$this->pageTitle=Yii::app()->name . ' - Error';
$this->breadcrumbs=array(
	'Error',
);
?>

<div class="custom-error">
    <h2>Ooops! Error <?php echo $code; ?></h2>
    <p class="err-message"><?php echo CHtml::encode($message); ?> Please contact your administrator.</p>
</div>