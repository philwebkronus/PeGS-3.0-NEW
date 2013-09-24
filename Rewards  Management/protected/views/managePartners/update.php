<?php
/* @var $this ManagePartnersController */
/* @var $model ManagePartners */

$this->breadcrumbs=array(
	'Manage Partners'=>array('index'),
	$model->PartnerID=>array('view','id'=>$model->PartnerID),
	'Update',
);

$this->menu=array(
	array('label'=>'List ManagePartners', 'url'=>array('index')),
	array('label'=>'Create ManagePartners', 'url'=>array('create')),
	array('label'=>'View ManagePartners', 'url'=>array('view', 'id'=>$model->PartnerID)),
	array('label'=>'Manage ManagePartners', 'url'=>array('admin')),
);
?>

<h1>Update ManagePartners <?php echo $model->PartnerID; ?></h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>