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
	'Administration','Menu Management',
);

?>

<h4>Menu Management</h4>

<?php echo CHtml::link("Create new menu", "#", array(
            'onclick'=>'$("#create-menu").dialog("open")'
        )); 
?>

<?php $gridData = array(
                    array('name'=>'Name',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Name"])',
                            'htmlOptions'=>array('style'=>'padding-left: 50px'),
                    ),
                    array('name'=>'Link',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Link"])',
                            'htmlOptions'=>array('style'=>'text-align: center'),

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
                                'url'=>'Yii::app()->createUrl("/menu/update", array("MenuID" => $data["MenuID"]))',
                                
//                                'options'=>array(
//                                    'ajax'=>array(
//                                        'type'=>'GET',
//                                        //'url'=>'js:$(this).attr("href")',
//                                        //'data'=>'array("MenuID" =>$data["Name"])',
//                                        'url'=>Yii::app()->createUrl("/menu/update",array("MenuID" =>$menu["Name"])),
//                                        'success'=>'function(data, textStatus, jqXHR){
//                                            $("#update-menu").html(data);
//                                            $("#update-menu").dialog("open");
//                                         }',
//                                        'error'=>'function(data){
//                                            alert(data)
//                                        }',
//                                    )
//                                )
                            ),
                            'buttonDisable'=>array(
                                'label'=>'Disable ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-disable.png',
                                'url'=>'Yii::app()->createUrl("/menu/changestatus", array("MenuID" => $data["MenuID"],"Status"=> $data["Status"]))',
                                'visible'=>'$data["Status"] == "Active"',
                            ),
                            'buttonEnable'=>array(
                                'label'=>'Enable ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-enable.png',
                                'url'=>'Yii::app()->createUrl("/menu/changestatus", array("MenuID" => $data["MenuID"],"Status"=> $data["Status"]))',
                                'visible'=>'$data["Status"] == "Inactive"',
                            ),
                            
                            /** Uncomment below to enable menu deletion **/
                            /*
                            'buttonDelete'=>array(
                                'label'=>'Delete ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-delete.png',
                                'url'=>'Yii::app()->createUrl("/menu/delete", array("MenuID" => $data["MenuID"]))',
                            ),*/

                        ),
                        'header'=>'Options',
                        //'htmlOptions'=>array('style'=>'width:80px'),
                    ),

                );

//Display datagrid
$this->widget('zii.widgets.grid.CGridView',array(
        'id'=>'data-grid',
        'dataProvider'=>$arrayDataProvider,
        'columns'=>$gridData,
        'ajaxUpdate'=>true,

)); ?>

<!-- Create menu dialog box -->

<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'create-menu',
        'options'=>array(
            'title'=>'New Menu',
            'modal'=>true,
            'width'=>'350',
            'height'=>'300',
            'resizable'=>false,
            'autoOpen'=>false,
            'buttons'=>array(
                'Create'=>'js:function(){
                    $("#CreateForm").submit();
                    $(this).dialog("close");
                }',
                'Cancel'=>'js:function(){
                    $(this).dialog("close");
                }'
            )
        ),
)); ?>

<?php echo $this->renderPartial('_form',array('menu'=>$menu,'action'=>'create')); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>

<!-- Update menu dialog box -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'update-menu',
        'options'=>array(
            'title'=>'Menu Update',
            'modal'=>true,
            'width'=>'350',
            'height'=>'300',
            'autoOpen'=>$this->updateDialog,
            'resizable'=>false,
            'buttons'=>array(
                'Update'=>'js:function(){
                    $("#UpdateForm").submit();
                    $("#update-menu").dialog("close");
                }',
                'Cancel'=>'js:function(){
                    $(this).dialog("close");
                    document.location="manage";
                }'
            )
        ),
)); ?>

<?php echo $this->renderPartial('_form',array('menu'=>$menu,'action'=>'update')); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
    
<!-- Delete menu dialog box -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'delete-menu',
        'options'=>array(
            'title'=>'Delete Menu',
            'modal'=>true,
            'width'=>'400',
            'height'=>'160',
            'resizable'=>false,
            'autoOpen'=>$this->deleteDialog,
            'buttons'=>array(
                'Delete'=>'js:function(){
                    $("#DeleteForm").submit();
                    $(this).dialog("close");
                }',
                'Cancel'=>'js:function(){
                    $(this).dialog("close");
                    document.location = "manage";
                }'
            )
        ),
)); ?>

<?php echo $this->renderPartial('_form',array('menu'=>$menu,'action'=>'delete')); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>

<!-- Disable menu dialog box -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'status-menu',
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
                    $(this).dialog("close");
                    document.location="manage";
                }'
            )
        ),
)); ?>

<?php echo $this->renderPartial('_form',array('menu'=>$menu,'action'=>'changeStatus')); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>