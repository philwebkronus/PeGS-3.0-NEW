<?php

/*
 * @Date Nov 20, 2012
 * @Author owliber
 */
?>

<?php Yii::app()->clientScript->registerScript('ui','
        
        var site = $("#Site"),
            egm = $("#EGM"),   
            datefrom = $("#DateFrom"),
            dateto = $("#DateTo"),
            vouchertype = $("#VoucherType"),
            status = $("#Status")
    
 ', CClientScript::POS_END);
 ?>

<?php echo CHtml::beginForm(); ?>    

    <?php
    
        if(Yii::app()->user->isPerSite())
        {
            echo CHtml::label("From ", "DateFrom");
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'DateFrom',
                'value'=>$this->dateFrom,
                'options'=>array(
                    'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
                    'showOn'=>'button', // 'focus', 'button', 'both'
                    'buttonText'=>Yii::t('ui','DateFrom'), 
                    'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
                    'buttonImageOnly'=>true,
                    'dateFormat'=>'yy-mm-dd',
                ),
                'htmlOptions'=>array(
                    'style'=>'width:80px;vertical-align:top'
                ),  
            ));

            echo CHtml::label(" To ", "DateTo");
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'DateTo',
                'value'=>$this->dateTo,
                'options'=>array(
                    'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
                    'showOn'=>'button', // 'focus', 'button', 'both'
                    'buttonText'=>Yii::t('ui','DateTo'), 
                    'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
                    'buttonImageOnly'=>true,
                    'dateFormat'=>'yy-mm-dd',
                ),
                'htmlOptions'=>array(
                    'style'=>'width:80px;vertical-align:top'
                ),  
            ));
            
            echo CHtml::label(" EGM ", "EGM");
            echo CHtml::dropDownList('EGM',$this->egmmachine, array('All'=>'All')+Stacker::listActiveEGMMachines(Yii::app()->user->getSiteID()));
            
            echo CHtml::label(" Type ", "VoucherType");
            echo CHtml::dropDownList("VoucherType", $this->vouchertype, Utilities::getVoucherTypes(), array(
                'id'=>'VoucherType',
            ));
            
            echo CHtml::label(" Status ", "Status");
            echo CHtml::dropDownList("Status", $this->status, Utilities::getVoucherStatus(), array(
                'id'=>'Status',
            ));
        }
        else
        {
            echo CHtml::label("From ", "DateFrom");
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'DateFrom',
                'value'=>$this->dateFrom,
                'options'=>array(
                    'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
                    'showOn'=>'button', // 'focus', 'button', 'both'
                    'buttonText'=>Yii::t('ui','DateFrom'), 
                    'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
                    'buttonImageOnly'=>true,
                    'dateFormat'=>'yy-mm-dd',
                ),
                'htmlOptions'=>array(
                    'style'=>'width:80px;vertical-align:top'
                ),  
            ));

            echo CHtml::label(" To ", "DateTo");
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'DateTo',
                'value'=>$this->dateTo,
                'options'=>array(
                    'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
                    'showOn'=>'button', // 'focus', 'button', 'both'
                    'buttonText'=>Yii::t('ui','DateTo'), 
                    'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
                    'buttonImageOnly'=>true,
                    'dateFormat'=>'yy-mm-dd',
                ),
                'htmlOptions'=>array(
                    'style'=>'width:80px;vertical-align:top'
                ),  
            ));
            
            echo CHtml::label(" Sites", "Site");
            echo CHtml::dropDownList('Site',$this->site, array('empty'=>'Select a site')+Stacker::listActiveSites(),array(
                    'id'=>'Site',
                    'ajax'=>array(
                        'type'=>'GET',
                        'url'=>Yii::app()->createUrl('stacker/ajaxEGMachines'),
                        'update'=>'#EGM',
                        'data'=>array(
                          'SiteID'=>'js:function(){return site.val();}'
                        ),
                    ),


            ));
            echo CHtml::label(" EGM ", "EGM");
            echo CHtml::dropDownList("EGM", $this->egmmachine, array('empty'=>"Select a site"), array(
                'id'=>'EGM',
            ));
            
            echo CHtml::label(" Type ", "VoucherType");
            echo CHtml::dropDownList("VoucherType", $this->vouchertype, Utilities::getVoucherTypes(), array(
                'id'=>'VoucherType',
            ));
            
            echo CHtml::label(" Status ", "Status");
            echo CHtml::dropDownList("Status", $this->status, Utilities::getVoucherStatus(), array(
                'id'=>'Status',
            ));
        }
        
    ?>   
        
    <?php
        echo CHtml::ajaxButton("Submit","ajaxVoucherUsage", array(
                    'type'=>'GET',                
                    'data'=>array(
                        'DateFrom'=>'js:function(){return datefrom.val();}',
                        'DateTo'=>'js:function(){return dateto.val();}',
                        'EGM'=>'js:function(){return egm.val();}',
                        'VoucherType'=>'js:function(){return vouchertype.val();}',
                        'Status'=>'js:function(){return status.val()}',
                        'Site'=>'js:function(){return site.val()}'
                    ),                    
                    'error'=>'function(data){ // if error occured
                        alert("There is a problem while processing the request");
                        $(".ui-dialog-titlebar").hide();
                        $("#ajaxloader").dialog("close"); 
                    }',
                    'beforeSend' => 'function(){
                        if(site.val() == "empty")
                        {
                            alert("Please select a site")
                            return false;
                        }
                        else
                        {
                            $(".ui-dialog-titlebar").hide()   
                            $("#ajaxloader").dialog("open")
                        }
                    }',
//                    'complete' => 'function(){
//                       
//                    }',
                    'success'=>'function(data){                        
                        $(".ui-dialog-titlebar").hide();
                        $("#ajaxloader").dialog("close"); 
                        $("#results-grid").html(data); 
                    }',
                    'update'=>'#results-grid',
                    ),
                    array(
                        'name'=>'Submit',
                        'id'=>'Submit',
                    )
      );
    ?>
 
<?php echo CHtml::endForm(); ?>