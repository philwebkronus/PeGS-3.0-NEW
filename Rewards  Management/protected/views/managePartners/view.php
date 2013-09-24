<?php
/* @var $this ManagePartnersController */
/* @var $model ManagePartners */

$this->breadcrumbs=array(
	'Manage Partners'=>array('index'),
	$model->PartnerID,
);

$this->menu=array(
	array('label'=>'List ManagePartners', 'url'=>array('index')),
	array('label'=>'Create ManagePartners', 'url'=>array('create')),
	array('label'=>'Update ManagePartners', 'url'=>array('update', 'id'=>$model->PartnerID)),
	array('label'=>'Delete ManagePartners', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->PartnerID),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage ManagePartners', 'url'=>array('admin')),
);
?>

<h1>View ManagePartners #<?php echo $model->PartnerID; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'PartnerID',
		'CompanyAddress',
		'CompanyEmail',
		'CompanyPhone',
		'CompanyFax',
		'CompanyWebsite',
		'ContactPerson',
		'ContactPersonPosition',
		'ContactPersonPhone',
		'ContactPersonMobile',
		'ContactPersonEmail',
		'NumberOfRewardOffers',
		'Option1',
		'Option2',
		'Option3',
	),
)); ?>
