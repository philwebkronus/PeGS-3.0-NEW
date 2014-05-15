<?php

/**
 * @author owliber
 * @date Oct 22, 2012
 * @filename menus.php
 * 
 */

?>

<?php

$this->breadcrumbs=array(
	'Administration','Sub Menu Management',
);

?>

<h4>Sub Menu Management</h4>
<hr color="black" />
<?php echo CHtml::link("Create new sub menu", "#", array(
            'onclick'=>'$("#create-submenu").dialog("open")'
        )); 
?>

<?php $gridData = array(
                    array('name'=>'Menu',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Menu"])',
                            'htmlOptions'=>array('style'=>'text-align:center'),
                    ),
                    array('name'=>'Sub Menu',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Submenu"])',
                            'htmlOptions'=>array('style'=>'padding-left: 50px'),
                    ),
                    array('name'=>'Link',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Link"])',
                            'htmlOptions'=>array('style'=>'padding-left: 50px'),

                    ),
                    array('name'=>'Status',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Status"])',
                            'htmlOptions'=>array('style'=>'text-align: center'),

                    ),
              
                    array('class'=>'CButtonColumn',
                        'template'=>'{buttonUpdate}{buttonDisable}{buttonEnable}',//{buttonDelete}',
                        'buttons'=>array(
                            'buttonUpdate'=>array(
                                'label'=>'Update ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-edit.png',
                                'url'=>'Yii::app()->createUrl("/submenu/update", array("SubMenuID" => $data["SubMenuID"]))',
                            ),
                            'buttonDisable'=>array(
                                'label'=>'Disable ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-disable.png',
                                'url'=>'Yii::app()->createUrl("/submenu/changestatus", array("SubMenuID" => $data["SubMenuID"],"Status"=> $data["Status"]))',
                                'visible'=>'$data["Status"] == "Active"',
                            ),
                            'buttonEnable'=>array(
                                'label'=>'Enable ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-enable.png',
                                'url'=>'Yii::app()->createUrl("/submenu/changestatus", array("SubMenuID" => $data["SubMenuID"],"Status"=> $data["Status"]))',
                                'visible'=>'$data["Status"] == "Inactive"',
                            ),
                            
                            /** Uncomment below to enable menu deletion **/
                            /*
                            'buttonDelete'=>array(
                                'label'=>'Delete ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-delete.png',
                                'url'=>'Yii::app()->createUrl("/submenu/delete", array("SubMenuID" => $data["SubMenuID"]))',
                            ),
                             * 
                             */

                        ),
                        'header'=>'Options',
                        //'htmlOptions'=>array('style'=>'width:80px'),
                    ),

                );
        
    //Display group grid view
    $this->widget('ext.groupgridview.GroupGridView', array(
      'id' => 'data-grid',
      'dataProvider' => $arrayDataProvider,
      'mergeColumns' => array('Menu'),
       'columns'=>$gridData,
    ));


 ?>

<!-- Create menu dialog box -->

<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'create-submenu',
        'options'=>array(
            'title'=>'New Sub Menu',
            'modal'=>true,
            'width'=>'400',
            'height'=>'350',
            'resizable'=>false,
            'autoOpen'=>false,
            'buttons'=>array(
                'Create'=>'js:function(){
                     $("#CreateForm").submit();
                     $(this).dialog("close");
                }',
                'Close'=>'js:function(){
                    $("#create-submenu").dialog("close");
                    document.location="manage";
                }'
            )
        ),
)); ?>

<?php echo $this->renderPartial('_form',array('action'=>'create')); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>


<!-- Update submenu dialog box -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'update-submenu',
        'options'=>array(
            'title'=>'Sub Menu Update',
            'modal'=>true,
            'width'=>'400',
            'height'=>'350',
            'autoOpen'=>$this->updateDialog,
            'resizable'=>false,
            'buttons'=>array(
                'Update'=>'js:function(){
                    $("#UpdateForm").submit();
                    $(this).dialog("close");    
                }',
                'Close'=>'js:function(){
                    $("#update-menu").dialog("close");
                    document.location="manage";
                }'
            )
        ),
)); ?>

<?php echo $this->renderPartial('_form',array('submenu'=>$submenu,'action'=>'update')); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
    
<!-- Delete menu dialog box -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'delete-submenu',
        'options'=>array(
            'title'=>'Delete Sub Menu',
            'modal'=>true,
            'width'=>'400',
            'height'=>'170',
            'resizable'=>false,
            'autoOpen'=>$this->deleteDialog,
            'buttons'=>array(
                'Delete'=>'js:function(){
                    $("#DeleteForm").submit();
                    $(this).dialog("close");
                }',
                'Close'=>'js:function(){
                    $("#delete-submenu").dialog("close");
                    document.location="manage";
                }'
            )
        ),
)); ?>

<?php echo $this->renderPartial('_form',array('submenu'=>$submenu,'action'=>'delete')); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>


<!-- Disable menu dialog box -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'status-submenu',
        'options'=>array(
            'title'=>'Update Status',
            'modal'=>true,
            'width'=>'400',
            'height'=>'180',
            'resizable'=>false,
            'autoOpen'=>$this->statusDialog,
            'buttons'=>array(
                'Update'=>'js:function(){
                    $("#ToggleForm").submit();
                    $(this).dialog("close");
                }',
                'Cancel'=>'js:function(){
                    $("#status-submenu").dialog("close");
                    document.location="manage";
                }'
            )
        ),
)); ?>

<?php echo $this->renderPartial('_form',array('submenu'=>$submenu,'action'=>'changeStatus')); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>