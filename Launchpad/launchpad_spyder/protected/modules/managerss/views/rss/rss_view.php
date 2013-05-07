<div class="ui-widget-content" style="max-width: 440px;">
    <?php
    $this->widget('application.modules.managerss.components.widgets.AjaxViewWidget',array('model'=>$model,'title'=>'NEWS & ANNOUNCEMENTS','attributes'=>array(
        'ID',
        'title',
//        'content',
        array(
            'label'=>'Content',
            'type'=>'raw',
            'value'=>'<div style="height:'.RssConfig::app()->params['max_height'].'; text-align:center">' . $model->content . '</div>',
        ),
        

    )));
    ?>
</div>