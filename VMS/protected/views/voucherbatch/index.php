<?php

/**
 * @author owliber
 * @date Nov 6, 2012
 * @filename index.php
 * 
 */
?>

<?php
$this->breadcrumbs=array(
	'Voucher Maintenance','Voucher Generation',
);

?>

<div id="batches">
    <h4> Generation Batches</h4>
    <span class="ui-icon ui-icon-document-b" style="display:inline-block"></span>
    <?php echo CHtml::link("Create New Batch", "#", array(
                'onclick'=>'$("#generate-voucher").dialog("open")'
            )); 
    ?>
</div>

<div id="details" style="display:none">
    <h4> Generated batch details</h4>
</div>

<div id="search-form" class="search-form" style="display:none">
    <!-- Render search filter -->
    <b>Search Filter</b><br /><br />
    <?php echo $this->renderPartial('_search'); ?>
</div>

<?php $gridData = array(
                    array('name'=>'BatchNumber',                        
                            'header'=>'Batch Number',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["BatchNumber"])',
                            'htmlOptions'=>array('style'=>'text-align:center',),
                    ),
                    array('name'=>'Quantity',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Quantity"])',
                            'htmlOptions'=>array('style'=>'padding-left: 50px; text-align: right'),
                    ),
                    array('name'=>'Amount',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Amount"])',
                            'htmlOptions'=>array('style'=>'padding-left: 50px; text-align: right'),

                    ),                    
                    array('name'=>'Total',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Total"])',
                            'htmlOptions'=>array('style'=>'padding-left: 50px; text-align: right'),

                    ),    
                    array('name'=>'DateGenerated',
                            'header'=>'Date Generated',
                            'type'=>'raw',
                            'value'=>'CHtml::encode(date("F d, Y H:i",strtotime($data["DateGenerated"])))',
                            'htmlOptions'=>array('style'=>'text-align: left'),

                    ),
                    array('name'=>'DateExpiry',
                            'header'=>'Expiry Date',
                            'type'=>'raw',
                            'value'=>'CHtml::encode(date("F d, Y ",strtotime($data["DateExpiry"])))',
                            'htmlOptions'=>array('style'=>'text-align: left'),

                    ),
              
                    //Option buttons
                    array('class'=>'CButtonColumn',
                        'template'=>'{buttonActivate}{buttonDeactivate}{buttonExport}{buttonDetails}',
                        'buttons'=>array(
                            'buttonActivate'=>array(
                                'label'=>'Activate ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-enable.png',
                                'url'=>'Yii::app()->createUrl("/voucherbatch/changestatus", array("BatchNo" => $data["BatchNumber"],"status"=>1))',
                                'visible'=>'VoucherGeneration::getBatchStatus($data["BatchNumber"]) == 0',
                                'options' => array(
                                    'ajax' => array(
                                        'type' => 'GET',
                                        'dataType'=>'json',
                                        'url' => 'js:$(this).attr("href")',
                                        'success' => 'function(data){
                                            if(data.activate == 1){
                                                var dialogData = \'<input id="BatchNo" type="hidden" name="BatchNo" value="\'+data.batchno+\'">\';
                                                    dialogData += \'<input id="Submit" type="hidden" name="Submit" value="Activate">\';
                                                    dialogData += \'<p>Are you sure you want to activate batch number \'+data.batchno+\' with a total of \'+data.qty+\' vouchers and denomination of \'+data.amount+\'?</p>\';
                                                $("#activate-dialog").html(dialogData);
                                                $("#activate-voucher").dialog("open");
                                            }
                                            
                                        }',
                                    ),

                                ),
                            ),
                            
                            'buttonDeactivate'=>array(
                                'label'=>'Deactivate ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-disable.png',
                                'url'=>'Yii::app()->createUrl("/voucherbatch/changestatus", array("BatchNo" => $data["BatchNumber"],"status"=>0))',
                                'visible'=> 'VoucherGeneration::getBatchStatus($data["BatchNumber"]) == 1',
                                 'options' => array(
                                    'ajax' => array(
                                        'type' => 'GET',
                                        'dataType'=>'json',
                                        'url' => 'js:$(this).attr("href")',
                                        'success' => 'function(data){
                                            if(data.activate == 0){
                                                var dialogData = \'<input id="BatchNo" type="hidden" name="BatchNo" value="\'+data.batchno+\'">\';
                                                    dialogData += \'<input id="Submit" type="hidden" name="Submit" value="Deactivate">\';
                                                    dialogData += \'<p>Are you sure you want to deactivate batch number \'+data.batchno+\' with a total of \'+data.qty+\' vouchers and denomination of \'+data.amount+\'?</p>\';
                                                $("#deactivate-dialog").html(dialogData);
                                                $("#deactivate-voucher").dialog("open");
                                            }
                                           
                                        }',
                                    ),

                                ),
                            ),
                            
                            'buttonExport'=>array(
                                'label'=>'Export to CSV ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-download.png',
                                'url'=>'Yii::app()->createUrl("/voucherbatch/exporttocsv", array("BatchNo" => $data["BatchNumber"]))',
                            ),
                            'buttonDetails'=>array(
                                'label'=>'Details ',
                                'imageUrl'=>Yii::app()->request->baseUrl.'/images/ui-icon-details.png',
                                'url'=>'Yii::app()->createUrl("voucherbatch/list",array("BatchNo"=>$data["BatchNumber"]))',
                                'options' => array(
                                    'ajax' => array(
                                        'type' => 'GET',
                                        'url' => 'js:$(this).attr("href")',
                                        'success'=>'function(data){                                            
                                            $("#data-grid").html(data); 
                                            $("#batches").hide();
                                            $("#details").toggle();
                                            $("#search-form").toggle();  
                                            $("#export-link").toggle(); 
                                        }',
                                    ),
                                    'update'=>'#data-grid'

                                ),
                            ),                            

                        ),
                        
                        'header'=>'Options',
                        'htmlOptions'=>array(
                            'style'=>'width:80px',
                        )
                    ),
                    
    

                );
        
    $this->widget('zii.widgets.grid.CGridView',array(
        'id'=>'data-grid',
        'dataProvider'=>$dataProvider,
        'columns'=>$gridData,
        //'htmlOptions'=>array(
        //        'style'=>'cursor: pointer;
        //    '),
       //'selectionChanged'=>"'+$(this).parent().parent().children(':nth-child(2)').text()+'?'",

    )); ?>


<?php
    /* Get the maximum quantity and amount for batch generation of vouchers */
    $max_qty = Utilities::getParameters('MAX_VOUCHER_BATCH_QTY');
    //$max_amt = Utilities::getParameters('MAX_VOUCHER_BATCH_AMT');
    
    /* Get the minimum quantity and amount */
    
    $min_qty = Utilities::getParameters('MIN_VOUCHER_BATCH_QTY');
    
    /* Get the array of amounts */
    
    $amt_list = trim(Utilities::getParameters('VOUCHER_AMT_LIST'));
    for( $i = 0 ; $i < count($amt_list); $i++ ){
        $arrlist = explode(",", $amt_list);
        $amt_list = '"'.implode('","', $arrlist).'"'; 
    }
       
?>

<!-- Generate Bulk Voucher dialog box -->

<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/custom.js'); ?>

<?php Yii::app()->clientScript->registerScript('validation','
       
        var arrlist = new Array('.$amt_list.');
            
        var quantity = $( \'#Quantity\' ),
            amount = $( \'#Amount\' ),
            allFields = $( [] ).add( quantity ).add( amount ),
            tips = $( \'.validateTips\' );
            
        function inArray(needle, haystack) {
            var length = haystack.length;
            for(var i = 0; i < length; i++) {
                if(haystack[i] == needle) return true;
            }
            return false;
        }
        
        function resetForm ( ) {
            tips
                .text ( "All form fields are required." )
                .addClass("validateTips")

            amount.val("");
            quantity.val("");
            $("p").removeClass("validateTips");
            $("input").removeClass("ui-state-error");
            

        }

        function updateTips( t ) {
            tips
                .text( t )
                .addClass( \'ui-state-highlight\' );
            setTimeout(function() {
                tips.removeClass( \'ui-state-highlight\', 1500 );
            }, 500 );
        }
                        
        function checkInput( o, n, min, max ) {
            if ( o.val() > max || o.val() < min) {
                o.addClass( \'ui-state-error\' );
                updateTips( \'Quantity should be between \' + min + \' and \'+ max);
                return false;
            } else {
                return true;
            }
        }
        
        function checkAmount(o) {
            if(inArray(o.val(), arrlist)){
                return true;
            } else {
                o.addClass( \'ui-state-error\' );
                updateTips( \'Amount only accepts \' + arrlist + \' denominations.\');
                return false;
            }
        }   
       
 ');
 ?>

<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'generate-voucher',
        'options'=>array(
            'title'=>'Generate new batch',
            'modal'=>true,
            'width'=>'400',
            'height'=>'380',
            'resizable'=>false,
            'autoOpen'=>false,
            'buttons'=>array(
                'Generate'=>'js:function(){                    
                    var bValid = true;
                    allFields.removeClass( "ui-state-error" );                    
                    bValid = bValid && checkInput(quantity, "quantity", '.$min_qty.', '.$max_qty.');
                    bValid = bValid && checkAmount(amount, '.$amt_list.');
                    
                    if ( bValid )
                    {
                        $("#confirm-message").dialog("open");
                        $("#generate-voucher").dialog("close");
                    }
                   
                }',
                'Cancel'=>'js:function(){
                    resetForm();
                    $(this).dialog("close");
               }',               
            ),
        ),
)); ?>

<?php echo CHtml::beginForm(array(
            'voucherbatch/generate'), 
            'POST', array(
                'id'=>'GenerateForm',
                'name'=>'GenerateForm')); ?>
 <fieldset>
 
<p class="validateTips">All form fields are required.</p>

<div class="row">
    <?php echo CHtml::label("Quantity","Quantity"); ?><br />
    <?php echo CHtml::textField("Quantity","",array(
            'style'=>'width: 80px; text-align: right;',
            'onkeypress'=>'return isNumberKey(event)')); ?>
</div>

<div class="row">
    <?php echo CHtml::label("Amount","Amount"); ?><br />
    <?php echo CHtml::textField("Amount","",array(
            'style'=>'text-align: right;',
            'onkeypress'=>'return isNumberKey(event)')); ?>
</div>

<div class="row">
    <?php echo CHtml::label("Validity","Validity"); ?><br />
    <?php echo CHtml::listBox("Validity", '30', array('15'=>'15 days','30'=>'30 days','60'=>'60 days')) ?>
</div>
 </fieldset>

<?php echo CHtml::endForm(); ?>

<!-- Toggle Voucher status -->
<?php $status_verb = $this->changeStatus == 0 ? 'activation' : 'deactivation'; ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>


<!-- Confirmation Dialog Box for Voucher generation -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'confirm-message',
        'options'=>array(
            'title'=>'Confirm Batch Generation',
            'modal'=>true,
            'width'=>'350',
            'height'=>'160',
            'resizable'=>false,
            'autoOpen'=>false,
            'buttons'=>array(
                'Continue'=>'js:function(){
                    $("#GenerateForm").submit();
                    $("#generate-voucher").dialog("close"); 
                    $("#confirm-message").dialog("close");  
                    $("#generate-message").dialog("open");
                }',
                'Cancel'=>'js:function(){
                     $("#confirm-message").dialog("close");
                     $("#generate-voucher").dialog("open"); 
                }'
            ),
            
        ),
)); ?>

<div class="row">
    <p>Are you sure you want to continue?</p>
</div>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
<!-- End Confirmation -->


<!-- Generate Message Dialog -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'generate-message',
        'options'=>array(
            'title'=>'Generation successful',
            'modal'=>true,
            'width'=>'350',
            'height'=>'160',
            'resizable'=>false,
            'autoOpen'=>$this->generateDialog,
            'buttons'=>array(
                'Close'=>'js:function(){
                    $("#generate-message").dialog("close");
                    window.location="manage";
                }'
            ),
        ),
)); ?>

<div class="row">
    <p>Voucher batch generation is successful.</p>
</div>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
<!-- Generate Message Dialog -->


<!-- Voucher Activation -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'activate-voucher',
        'options'=>array(
            'title'=>'Activate Vouchers',
            'modal'=>true,
            'width'=>'400',
            'height'=>'200',
            'resizable'=>false,
            'autoOpen'=>false,
            'buttons'=>array(
                'Activate'=>'js:function(){
                    $("#ActivateForm").submit();
                    $(this).dialog("close"); 
                }',               
                'Cancel'=>'js:function(){
                    $("#activate-voucher").dialog("close");
                }'
                
            )
        ),
)); ?>

<?php echo CHtml::beginForm(array('voucherbatch/changestatus'), 'POST', array(
        'id'=>'ActivateForm',
        'name'=>'ActivateForm')); ?>

<div id="activate-dialog"></div>

<?php echo CHtml::endForm(); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
<!-- End Voucher Activation -->


<!-- Voucher Deactivation -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'deactivate-voucher',
        'options'=>array(
            'title'=>'Deactivate Vouchers',
            'modal'=>true,
            'width'=>'400',
            'height'=>'200',
            'resizable'=>false,
            'autoOpen'=>false,
            'buttons'=>array(
                'Deactivate'=>'js:function(){
                    $("#DeActivateForm").submit();
                    $(this).dialog("close"); 
                }',               
                'Cancel'=>'js:function(){
                    $("#deactivate-voucher").dialog("close");
                }'
                
            )
        ),
)); ?>

<?php echo CHtml::beginForm(array('voucherbatch/changestatus'), 'POST', array(
        'id'=>'DeActivateForm',
        'name'=>'DeActivateForm')); ?>

<div id="deactivate-dialog"></div>

<?php echo CHtml::endForm(); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
<!-- End Voucher Deactivation -->

<!-- Message Dialog after activation/deactivation -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'message-dialog',
        'options'=>array(
            'title'=>'Activation/Deactivation',
            'modal'=>true,
            'width'=>'350',
            'height'=>'150',
            'resizable'=>false,
            'autoOpen'=>$this->messageDialog,
            'buttons'=>array(
                'Close'=>'js:function(){
                    $(this).dialog("close");
                    window.location="manage";
                }'
            )
        ),
)); ?>

<div class="row">
    <p>Voucher <?php echo $status_verb; ?> is successful.</p>
</div>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
<!-- End message dialog -->

<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'ajaxloader',
        'options'=>array(
            'title'=>'Loading',
            'modal'=>true,
            'width'=>'200',
            'height'=>'45',
            'resizable'=>false,
            'autoOpen'=>false,
        ),
)); ?>

<div class="loading"></div><div class="loadingtext">Loading, please wait...</div>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>