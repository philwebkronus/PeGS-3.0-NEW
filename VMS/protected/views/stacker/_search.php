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
 <table style="width: 700px">
        <tr>
            <td>
                <?php
    
        if(Yii::app()->user->isPerSite())
        {
            echo CHtml::label("EGM Machines ", "EGM");
            echo CHtml::dropDownList('EGM',$this->egmmachine, array('All'=>'All')+Stacker::listActiveEGMMachines(Yii::app()->user->getSiteID()));
        }
        else
        {    
            echo CHtml::label("eGames : ", "Site");
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
            echo CHtml::label(" EGM : ", "EGM");
            echo CHtml::dropDownList("EGM", $this->egmmachine, array('empty'=>"Select a machine"), array(
                'id'=>'EGM','style'=>'width: 150px'
            ));
        }
        
    ?> 
          </td>       
       <td>
        
    <?php
        echo CHtml::ajaxButton("Submit", "ajaxStackerSessions", array(
                    'type'=>'GET',                
                    'data'=>array(
                        'Site'=>'js:function(){return site.val();}',
                        'EGM'=>'js:function(){return egm.val();}',
                        'IsAdvance'=>0,
                    ),
                    'success'=>'function(data){
                        if(data == "Date must not be greater than today." || data == "Please select a site and EGM then try again."){
                        alert(data);
                        }
                        else{
                        $("#results-grid").html(data); 
                        }
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
    </td>
    <td>
    <span class="ui-icon ui-icon-search" style="display:inline-block;"></span>
     
    <?php echo CHtml::link(" Advance Search", "", array(
            'onclick'=>'$("#advance-search").toggle();$("#Submit").toggle();$(this).hide();$("#hide-as").show();$("#advance-search-lbl").show()',
            'style'=>'cursor:pointer',
            'id'=>'show-as'
    )); ?>
    </td>
    <td>
    <?php echo CHtml::link(" Hide Advance Search", "", array(
            'onclick'=>'$("#advance-search").toggle();$("#Submit").toggle();$("#show-as").show(); $(this).hide();$("#advance-search-lbl").hide()',
            'style'=>'cursor:pointer; display:none',
            'id'=>'hide-as'
    )); ?>    
    </td>    
</tr>
</table>
    
        
<?php $display = $this->advanceFilter == true ? 'block' : 'none'; ?>

<div id="advance-search" style="display:<?php echo $display; ?>">
    <br/>
    <table style="width: 600px">
        <tr>
            <td><?php echo CHtml::label("From :", "dateFrom");?></td>
            <td>
    <?php
   echo CHtml::textField('DateFrom', date('Y-m-d'), array('id'=>'DateFrom','readonly'=>'true', 'value'=>date('Y-m-d'), 'style'=>'width: 100px;')).
        CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton","class"=>"pointer","style"=>"cursor: pointer;"));
        $this->widget('application.extensions.calendar.SCalendar',
        array(
        'inputField'=>'DateFrom',
        'button'=>'calbutton',
        'showsTime'=>false,
        'ifFormat'=>'%Y-%m-%d',
        )); 
    ?>
         </td>
         <td><?php  echo CHtml::label("To :", "dateTo");?></td>
         <td>
    <?php            
   echo CHtml::textField('DateTo', date('Y-m-d'), array('id'=>'DateTo','readonly'=>'true', 'value'=>date('Y-m-d'), 'style'=>'width: 100px;')).
        CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton2","class"=>"pointer","style"=>"cursor: pointer;"));
        $this->widget('application.extensions.calendar.SCalendar',
        array(
        'inputField'=>'DateTo',
        'button'=>'calbutton2',
        'showsTime'=>false,
        'ifFormat'=>'%Y-%m-%d',
        ));
    ?>
    </td>
    <td>
    <?php
    echo CHtml::checkBox("Session", $this->stackersession == false ? false : true);
    echo CHtml::label("Include ended sessions", "Session")
    ?>
    </td>
    </tr>
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
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
                        if(data == "Date must not be greater than today." || data == "Please select a site and EGM then try again."){
                        alert(data);
                        }
                        else{
                        $("#results-grid").html(data); 
                        }
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
</div>
<?php echo CHtml::endForm(); ?>