<style type="text/css">
    
.vip-button{background-color:#136510 !important;; border:none; padding: 8px 12px; text-transform: uppercase; cursor: pointer; color:#FFFFFF;}
.vip-button:hover{background-color:#2a8b24 !important;}
a.vip-button{background-color:#136510 !important;; border:none; padding: 8px 12px; text-transform: uppercase; cursor: pointer; color:#FFFFFF;}
a.vip-button:hover{text-decoration: none; background-color:#2a8b24 !important;}

</style>

<div>
    
</div>
<div class="form wide" style="padding:10px;">
    
    <?php if($type == 1)
        
        {
            echo "<h1><div style='color:#053304; min-width: 400px;' class='ui-widget-header ui-corner-all centerText'>$title</div></h1> ";
        }
        else if( $type == 2) { //This is exclusive use for Content Management
            
            echo "<h1><div style='color:#053304; min-width: 420px;' class='ui-widget-header ui-corner-all centerText'>$title</div></h1> ";
            
        }
        else
        {
            
            //$message = "We have encountered some technical problems. <br />Kindly email as screenshot(s) and request details.";
            
            if( preg_match( "/array is empty/", $message) ) $message = "No available record";
            
            echo "<h1><div style='color:red; min-width: 400px;' class='ui-widget-header ui-corner-all centerText'>$title</div></h1> ";
        }
        
    ?>
  

<?php  $form=$this->beginWidget('CActiveForm', 
        array( 'id'=>'ajax-form',
            'action'=>'',
            'enableClientValidation'=>true, 
            'clientOptions'=>array(
                    'validateOnSubmit'=>true,
            ),)); ?>
    
        <div class="row" align="center">
            
           <h2> <?php echo $message; ?></h2>
           
        </div> 
    
        <div class="row" align="center">
            
        <?php 
        
        if($type== 1)
        {
            
            if($isCashier == 0) {
            
                echo CHtml::button('Close',array(
                                            'style'=>'text-align:center',                                       
                                            'onClick'=> 'window.location.reload()',
                                            'class'=>'btnCancel vip-button'
                                        ));         
            
            }
            else if ($isCashier == 2) { 
                
                echo CHtml::button('Close',array(
                                            'style'=>'text-align:center',                                       
                                            'onClick'=> '$.fancybox.close();',
                                            'class'=>'btnCancel vip-button'
                                        )); 
                
            }
            else {
                
                echo CHtml::button('Close',array(
                                            'style'=>'text-align:center',                                       
                                            'onClick'=> 'window.close()',
                                            'class'=>'btnCancel vip-button'
                                        ));   
                
            }
            
        }
        else if ($type == 2) { //This is exclusive use for Content Management
            
            echo CHtml::button('Yes',array(
                                            'style'=>'text-align:center',
                                            'class'=>'vip-button',
                                            'onclick' => "
                                                (function(){
                                                    $.fancybox({
                                                        href : '{$this->link}/id/$this->id/confirmed/1',
                                                        modal : true
                                                    });
                                                })(event)
                                             "
                                        ));
            
            echo "&nbsp;&nbsp;&nbsp;&nbsp;";
            
            echo CHtml::button('No',array(
                                            'style'=>'text-align:center',                                       
                                            'onClick'=> 'window.location.reload()',
                                            'class'=>'btnCancel vip-button'
                                        ));
            
        }
        else
        {
            if($isCashier == 0) {
            
                if($refresh == 0) {

                    echo CHtml::button('Close',array(
                                                'style'=>'text-align:center',                                       
                                                'onClick'=>'$.fancybox.close()',
                                                'class'=>'btnCancel vip-button'
                                            ));    

                }
                else {

                    echo CHtml::button('Close',array(
                                                'style'=>'text-align:center',                                       
                                                'onClick'=>'window.location.reload();',
                                                'class'=>'btnCancel vip-button'
                                            )); 

                }
            
            }
            else {
                
                echo CHtml::button('Close',array(
                                                'style'=>'text-align:center',                                       
                                                'onClick'=>'window.close();',
                                                'class'=>'btnCancel vip-button'
                                            )); 
                
            }
            
        }
        
        ?>
        </div>
               
        
    <?php $this->endWidget(); ?>
    
    </div>

<script type="text/javascript">
    
    $(document).ready(function(){ 

        /**
        * Deprecated as of August 22, 2012
        * Generates error:
        * TypeError: this.element.propAttr is not a function this.options.disabled = !!this.element.propAttr( "disabled" );
        * 
        */
        //$('.btnCancel').button();

    });

</script>

