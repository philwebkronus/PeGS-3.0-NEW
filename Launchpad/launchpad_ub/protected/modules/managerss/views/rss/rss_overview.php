<table id="grid1"></table>
<div id="pager1"></div>
<br />
<button class="btnAdd" href="<?php echo $this->createUrl('create') ?>">Add</button>
<br /><br />
<a class="btnLogout" href="<?php echo Yii::app()->createUrl('/managerss/auth/logout') ?>">Logout</a>
<?php
Yii::app()->clientScript->registerScript("addbtn","
    $('.btnAdd').button({icons: {primary: 'ui-icon-plusthick'}});
    $('.btnLogout').button({icons: {primary: 'ui-icon-locked'}});
");

$updateUrl = $this->createUrl('update',array('id'=>'')) . ((Yii::app()->urlManager->urlFormat == 'path')?'/':'');
$viewUrl = $this->createUrl('view',array('id'=>'')) . ((Yii::app()->urlManager->urlFormat == 'path')?'/':'');
$deleteUrl = $this->createUrl('delete',array('id'=>'')) . ((Yii::app()->urlManager->urlFormat == 'path')?'/':'');

$this->widget('application.modules.managerss.components.widgets.JqGridWidget',array('tableID'=>'grid1','pagerID'=>'pager1',
    
    'jqGridParam'=>array(
        'url'=>$this->createUrl('overview'),
        'caption'=>'List',
//        'height'=>'200px',
        'colNames'=>array('Order','Title','Content',''),
        'colModel'=>array(
            array('name'=>'Order','width'=>'8%','sortable'=>false),
            array('name'=>'Title','width'=>'20%','sortable'=>false),
            array('name'=>'Content','width'=>'65%','sortable'=>false),
            array('name'=>'Action','width'=>'7%','sortable'=>false,'resizable'=>false),
        ),
        'loadComplete'=>"
            function(data) {
                if(data.rows == undefined)
                    return false;
                for(i=0;i<data.rows.length;i++) {
                    var lastRow = $('#'+data.rows[i].id).children().length;
                    var viewBtn = '<div title=\"View\" href=\"$viewUrl'+data.rows[i].id+'\"  class=\"btnView iconBtn ui-widget ui-state-default ui-corner-all\"><span class=\"ui-icon ui-icon-search\"></span></div>';
                    var editBtn = '<div title=\"Edit\" href=\"$updateUrl'+data.rows[i].id+'\"  class=\"btnUpdate iconBtn ui-widget ui-state-default ui-corner-all\"><div class=\"ui-icon ui-icon-pencil\"></div></div>';
                    var deleteBtn = '<div title=\"Delete\" href=\"$deleteUrl'+data.rows[i].id+'\"  class=\"btnDelete iconBtn ui-widget ui-state-default ui-corner-all\"><div class=\"ui-icon ui-icon-trash\"></div></div>';
                    $('#'+data.rows[i].id+'> td:nth-child('+lastRow+')').html(viewBtn+editBtn+deleteBtn);
                }
            }
        "
)));

//    $this->widget('application.modules.managerss.components.cleditor.ECLEditor', array(
//        'name'=>'field',
////        'model'=>$model,
//        'attribute'=>'fieldName', //Model attribute name. Nome do atributo do modelo.
//        'options'=>array(
//            'width'=>'600',
//            'height'=>250,
//            'useCSS'=>true,
//        ),
////        'value'=>$model->fieldName, //If you want pass a value for the widget. I think you will. Se você precisar passar um valor para o gadget. Eu acho irá.
//    ));

//$this->widget('application.modules.managerss.components.tinymce.ETinyMce', array('name'=>'html'));