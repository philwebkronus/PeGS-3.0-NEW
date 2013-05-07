<?php $this->beginContent('/layouts/main'); ?>
<br />
<div class="span-20">
    <?php echo $content; ?>
</div>
<div class="span-4 last">
    <?php
        $this->widget('application.modules.managerss.components.widgets.SideMenuWidget',array(
            'activateItemsOuter'=>true,
            'linkLabelWrapper' => 'div',
            'activateItems' => true,
            'items'=>array(
                array('label'=>'Add','url'=>array('/managerss/rss/add'),'icon'=>'ui-icon-plus'),
                array('label'=>'Update','url'=>array('/managerss/rss/update'),'icon'=>'ui-icon-pencil'),
                array('label'=>'View','url'=>array('/managerss/rss/view'),'icon'=>'ui-icon-search'),    
                array('label'=>'Delete','url'=>array('/managerss/rss/delete'),'icon'=>'ui-icon-trash')
            )
        ));
        
//        $this->widget('zii.widgets.jui.CJuiAccordion', array(
//            'panels'=>array(
//                'panel 1'=>'content for panel 1',
//                'panel 2'=>'content for panel 2',
//                // panel 3 contains the content rendered by a partial view
//            ),
//            // additional javascript options for the accordion plugin
//            'options'=>array(
//                'animated'=>'bounceslide',
//            ),
//        ));

    ?>
</div>
<div class="clear"></div>
<?php $this->endContent(); ?>