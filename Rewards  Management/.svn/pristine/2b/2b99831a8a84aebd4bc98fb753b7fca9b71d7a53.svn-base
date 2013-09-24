<h1 class="ui-widget-header centerText"><?php echo $this->title;?></h1>

<div class="append-1 prepend-1">
    
<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>$attributes,
        'itemCssClass'=>array('ui-state-default','ui-state-active'),
)); ?>
    
<br />

<div><?php echo CHtml::button('Close', array('class'=>'btnCancel','title'=>'Close')) ?></div>

<script type="text/javascript">

    $(document).ready(function(){
        $('.btnCancel').button();
    });

</script>
</div>
<br />