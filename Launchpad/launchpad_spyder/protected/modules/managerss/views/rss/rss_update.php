<div class="ui-widget-content">
    <h1 class="ui-widget-header centerText">UPDATE NEWS &amp; ANNOUNCEMENTS</h1>
    <div class="append-1 prepend-1">
        <?php $this->renderPartial('rss_form',array('model'=>$model,'type'=>'update','url'=>$this->createUrl('update',array('id'=>$model->ID)))) ?>
    </div>
</div>
