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
            stackersession = $("#Session")
            
        function checkInput(){
        
        /*
            if(site.val().length > 0 && site.val() != "empty")
            {
                document.getElementById("Submit").disabled  = false;
                document.getElementById("Search").disabled  = false;
            }
            else
            {
                document.getElementById("Submit").disabled = true;
                document.getElementById("Search").disabled = true;
            }
            
            if(egm.val().length > 0 && egm.val() != "empty")
            {
                
                document.getElementById("Submit").disabled  = false;
                document.getElementById("Search").disabled  = false;

            } */
            
            if(egm.val() == "empty" && site.val() == "empty")
            {
                alert("Please select site and machine");
             }
            
        }
    
 ', CClientScript::POS_END);
 ?>

<?php echo CHtml::beginForm(); ?>    

 <h5 id="advance-search-lbl" style="display:none">Advance Search</h5>
    <?php
    
        if(Yii::app()->user->isPerSite())
        {
            echo CHtml::label("EGM Machines ", "EGM");
            echo CHtml::dropDownList('EGM',$this->egmmachine, array('All'=>'All')+Stacker::listActiveEGMMachines(Yii::app()->user->getSiteID()));
        }
        else
        {
            echo CHtml::label("Sites", "Site");
            echo CHtml::dropDownList('Site',$this->site, array('empty'=>'Select a site')+Stacker::listActiveSites(),array(
                    'id'=>'Site',
                    //'onchange'=>'checkInput()',
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
        }
        
    ?>   
        
    <?php
        echo CHtml::ajaxButton("Submit", "ajaxStackerSessions", array(
                    'type'=>'GET',                
                    'data'=>array(
                        'Site'=>'js:function(){return site.val();}',
                        'EGM'=>'js:function(){return egm.val();}',
                        'IsAdvance'=>0,
                    ),
                    'success'=>'function(data){
                        $("#results-grid").html(data); 
                    }',
                    'error'=>'function(data){ // if error occured
                        alert("Please select a site and EGM then try again");
                    }',
                    'beforeSend' => 'function(){
                        $(".ui-dialog-titlebar").hide()   
                        $("#ajaxloader").dialog("open")
                    }',
                    'complete' => 'function(){
                       $(".ui-dialog-titlebar").hide()   
                       $("#ajaxloader").dialog("close")
                    }',
                    'update'=>'#results-grid',
                    ),
                    array(
                        'name'=>'Submit',
                        'id'=>'Submit',
                        //'disabled' => 'disabled',
                    )
      );
    ?>
    <span class="ui-icon ui-icon-search" style="display:inline-block;"></span>
     
    <?php echo CHtml::link(" Advance Search", "", array(
            'onclick'=>'$("#advance-search").toggle();$("#Submit").toggle();$(this).hide();$("#hide-as").show();$("#advance-search-lbl").show()',
            'style'=>'cursor:pointer',
            'id'=>'show-as'
    )); ?>
    
    <?php echo CHtml::link(" Hide Advance Search", "", array(
            'onclick'=>'$("#advance-search").toggle();$("#Submit").toggle();$("#show-as").show(); $(this).hide();$("#advance-search-lbl").hide()',
            'style'=>'cursor:pointer; display:none',
            'id'=>'hide-as'
    )); ?>
        
<?php $display = $this->advanceFilter == true ? 'block' : 'none'; ?>

<div id="advance-search" style="display:<?php echo $display; ?>">
    
    <?php
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
    
    echo CHtml::checkBox("Session", $this->stackersession == false ? false : true);
    echo CHtml::label("Include ended sessions", "Session")
    ?>
    
    <?php
        echo CHtml::ajaxButton("Search", "ajaxStackerSessions", array(
                    'type'=>'GET',                
                    'data'=>array(
                        'Site'=>'js:function(){return site.val();}',
                        'EGM'=>'js:function(){return egm.val();}',
                        'DateFrom'=>'js:function(){return datefrom.val();}',
                        'DateTo'=>'js:function(){return dateto.val();}',
                        'StackerSession'=>'js:function(){
                            if(stackersession[0].checked)
                                return 1;
                            else
                                return 0;
                        }',
                        'IsAdvance'=>1,
                    ),
                    'success'=>'function(data){
                        $("#results-grid").html(data); 
                    }',
                    'error'=>'function(data){ // if error occured
                        alert("Please select a site and EGM then try again");
                    }',
                    'beforeSend' => 'function(){
                        $(".ui-dialog-titlebar").hide()   
                        $("#ajaxloader").dialog("open")
                    }',
                    'complete' => 'function(){
                       $(".ui-dialog-titlebar").hide()   
                       $("#ajaxloader").dialog("close")
                    }',
                    'update'=>'#results-grid',
                    ),
                    array(
                        'name'=>'Search',
                        'id'=>'Search',
                        //'disabled' => 'disabled',
                    )
      );
    ?>
</div>
<?php echo CHtml::endForm(); ?>