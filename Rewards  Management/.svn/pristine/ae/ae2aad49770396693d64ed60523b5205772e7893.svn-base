<br />
<div class="form wide">
    <?php 
    $form=$this->beginWidget('application.components.widgets.CActiveLiveForm',array(
            'id'=>'ajax-form',
            'enableAutoScript'=>true,
            'action'=>$url,
            'enableClientValidation'=>true,
            'clientOptions'=>array(
                    'validateOnSubmit'=>true,
                    'afterValidateAttribute' => "function(){ alert(123);}"
            ),
    ));
    
    ?>
    <p class="note">Fields with <span class="required">*</span> are required.</p>
    <?php foreach($attributes as $attrName => $options): ?> 
    <div class="row">
        <?php 
            if(!is_array($options)) {
                $attrName = $options;
                $opt = array();
            } else {
                $opt = $options;
            }
        
            if(!isset($opt['htmlOptions'])) 
                $opt['htmlOptions'] = array();
            
            if(!isset($opt['htmlOptions']['title']))
                $opt['htmlOptions']['title'] = $attrName;
        ?>
           
        <?php 
            if(!isset($opt['type']) || (isset($opt['type']) && $opt['type'] != 'hiddenField' || $opt['type']=='raw')) {
                echo $form->labelEx($model,$attrName);
            }
        ?>
        <?php if(!isset($opt['type'])): ?>
            <?php 
                $opt['type'] = 'textField';
                $opt['htmlOptions'] = array_merge(array('title'=>$attrName), $opt['htmlOptions']); 
                if(!isset($opt['htmlOptions']['class'])) {
                    $opt['htmlOptions']['class'] = 'ui-state-default ui-corner-all ui-widget';
                }
                echo $form->textField($model,$attrName,$opt['htmlOptions']);
            ?>
        <?php else: ?>
            <?php 
            switch($opt['type']) {
                case 'textField':
                    if(!isset($opt['htmlOptions']['class'])) {
                        $opt['htmlOptions']['class'] = 'ui-state-default ui-corner-all ui-widget';
                    }
                    echo $form->textField($model,$attrName,$opt['htmlOptions']);
                    break;
                case 'passwordField':
                    if(!isset($opt['htmlOptions']['class'])) {
                        $opt['htmlOptions']['class'] = 'ui-state-default ui-corner-all ui-widget';
                    }
                    echo $form->passwordField($model,$attrName,$opt['htmlOptions']);
                    break;
                case 'textArea':
                    if(!isset($opt['htmlOptions']['class'])) {
                        $opt['htmlOptions']['class'] = 'ui-state-default ui-corner-all ui-widget';
                    }
                    echo $form->textArea($model,$attrName,$opt['htmlOptions']);
                    break;
                case 'radioButtonList':
                    echo $form->radioButtonList($model,$attrName,$opt['data'],$opt['htmlOptions']);
                    break;
                case 'raw':
                    echo $opt['value'];
                    break;
                case 'hiddenField':
                    echo $form->hiddenField($model,$attrName,$opt['htmlOptions']);
                    break;
                case 'dropDownList':
                    if(!isset($opt['htmlOptions']['class'])){
                        $opt['htmlOptions']['class'] = '';
                    }                    
                    echo $form->dropDownList($model, $attrName, $opt['data'],$opt['htmlOptions']);
                    break;
                    
            }
            
            ?>
        <?php endif; ?>
        <?php 
            if($opt['type'] != 'hiddenField' && $opt['type'] !='raw') {
                echo $form->error($model,$attrName);
            }
        ?>
    </div>
    <?php endforeach; ?>
    <div class="clear"></div>
    <br />
    <div>
    <?php 

        if(isset($type) && $type == 'new'):
            echo CHtml::submitButton('Create',array('class'=>'btnAdd','title'=>''));
        else:
            echo CHtml::submitButton('Update',array('class'=>'btnUpdate updateBtn','title'=>''));
        endif;
            echo CHtml::button('Cancel', array('class'=>'btnCancel right','title'=>''));

        $this->endWidget(); 

    ?>
</div><!-- form -->
<script type="text/javascript">
/*<![CDATA[*/
$(document).ready(function(){
    $('.btnAdd').button();
    $('.btnCancel').button();
    $('.updateBtn').button();    
    $('select[scope="light-select"]').each(function(i){
    $(this).selectmenu({style:'dropdown',speed:'1',maxHeight: 100,width: 200});  
        

    
    
});
});
$('#fancybox-wrap').load(function(){


    
});


/*]]>*/
</script>