<?php
$this->layout = '//layouts/login';
$this->pageTitle=Yii::app()->name . ' - Login';

Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/validations.js');
Yii::app()->clientScript->registerScript("validation","
             jQuery(document).ready(function(){
                  jQuery(':text').live('cut copy paste',function(e) {
                      e.preventDefault();
                  });

                  jQuery(':password').live('cut copy paste',function(e) {
                      e.preventDefault();
                  });
                  
                  jQuery('#browser').val(jQuery.browser.msie);
                  jQuery('#version').val(jQuery.browser.version);
                  jQuery('#chrome').val(jQuery.browser.safari);
                  jQuery('#UserName').focus();
                    
                  //this will disable the right click
                   var isNS = (navigator.appName == 'Netscape') ? 1 : 0;
                   if(navigator.appName == 'Netscape') document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);
                   function mischandler(){
                        return false;
                   }
                   function mousehandler(e){
                         var myevent = (isNS) ? e : event;
                         var eventbutton = (isNS) ? myevent.which : myevent.button;
                         if((eventbutton==2)||(eventbutton==3)) return false;
                   }
                   document.oncontextmenu = mischandler;
                   document.onmousedown = mousehandler;
                   document.onmouseup = mousehandler;
             });
             
             function preventBackandForward()
             {
                 window.history.forward();
             }
             preventBackandForward();
             window.inhibited_load=preventBackandForward;
             window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
             window.inhibited_unload=function(){void(0);};
",CClientScript::POS_HEAD);

?>

<div class="form login">
    <div class="login-title"><?php echo $this->pageTitle; ?></div>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note">Only authorized users are allowed.</p>

	<div class="row">
		<?php echo $form->labelEx($model,'UserName'); ?>
		<?php echo $form->textField($model,'UserName',array('onkeypress' => 'return numberandletter(event);', 'maxlength' => 20)) ?>
		<?php //echo $form->error($model,'UserName'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'Password'); ?>
		<?php echo $form->passwordField($model,'Password',array('onkeypress' => 'return numberandletter(event);', 'maxlength' => 12)); ?>
		<?php //echo $form->error($model,'Password'); ?>
	</div>
	<div class="row buttons">
		<?php echo CHtml::submitButton('Login', array('id' =>'btnsubmit')); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'message',
    'options'=>array(
        'autoOpen'=>$this->showDialog,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'buttons' => array
        (
            'OK'=>'js:function(){$(this).dialog("close");}',
        ),
    ),
));
echo $this->dialogMsg;
echo "<br/>";
$this->endWidget('zii.widgets.jui.CJuiDialog');

?>
