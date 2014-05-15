<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<h4>System Options</h4>
<hr color="black" />
<?php $gridData = array(
                    array('name'=>'Name',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["ParamName"])',
                            'htmlOptions'=>array('style'=>'text-align:left'),
                    ),
                    array('name'=>'Value',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["ParamValue"])',
                            'htmlOptions'=>array('style'=>'text-align:center;'),
                    ),
                    array('name'=>'Description',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["ParamDesc"])',

                    ),
    
                    array('class'=>'CButtonColumn',
                        'template'=>'{buttonUpdate}',//{buttonDelete}',
                        'buttons'=>array(
                            'buttonUpdate'=>array(
                                'label'=>'Update ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-edit.png',
                                'url'=>'Yii::app()->createUrl("/options/update", array("ParamID" => $data["ParamID"]))',
                            ),
                            
                            /** Uncomment below to enable menu deletion **/
                            /*
                            'buttonDelete'=>array(
                                'label'=>'Delete ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-delete.png',
                                'url'=>'Yii::app()->createUrl("/options/delete", array("ParamID" => $data["ParamID"]))',
                            ),
                             * 
                             */
                        ),
                        'header'=>'Options',
                    ),

                );
        
    //Display group grid view
    $this->widget('zii.widgets.grid.CGridView',array(
        'id'=>'data-grid',
        'dataProvider'=>$arrayDataProvider,
        'columns'=>$gridData,
        
    ));
?>

<!-- Update parameter -->

<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'parameter-id',
        'options'=>array(
            'title'=>'Update Parameter',
            'modal'=>true,
            'width'=>'500',
            'height'=>'300',
            'resizable'=>false,
            'autoOpen'=>$this->updateDialog,
            'buttons'=>array(
                'Update'=>'js:function(){
                    $("#OptionForm").submit();
                    $(this).dialog("close");
                }',
                'Cancel'=>'js:function(){
                    $(this).dialog("close");
                    document.location="manage";
                }'
            )
        ),
)); ?>
<?php if($this->updateDialog):?>
<table>
<?php echo CHtml::beginForm('manage', 'POST', array('id'=>'OptionForm','name'=>'OptionForm')); ?>
    <tr>
        <td>
            <?php echo CHtml::label("Parameter name", "Name"); ?>
        </td>
        <td>
            <?php echo CHtml::textField("Name",$param['ParamName'],array('style'=>'width:250px')); ?>
        </td>
    </tr>
<?php echo CHtml::hiddenField("Submit", 'Update'); ?>
<?php echo CHtml::hiddenField("ParamID", $param['ParamID']); ?>
    <tr>
        <td>
            <?php echo CHtml::label("Parameter value", "Value"); ?>
        </td>
        <td>
            <?php echo CHtml::textField("Value",$param['ParamValue'],array('style'=>'width:250px')); ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php echo CHtml::label("Description", "Decription"); ?>
        </td>
        <td>
            <?php echo CHtml::textArea("Description",$param['ParamDesc'],array('cols'=>'35')); ?>
        </td>
    </tr>
    </table>
<?php echo CHtml::endForm(); ?>
<?php endif; ?>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
