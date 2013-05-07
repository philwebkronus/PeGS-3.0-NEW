<div class="ui-widget-content ">
    <h1 class="ui-widget-header centerText">CREATE NEWS &amp; ANNOUNCEMENTS</h1>
    <div class="append-1 prepend-1">
        <?php $this->renderPartial('rss_form',array('model'=>$model,'url'=>$this->createUrl('create'),'type'=>'new')) ?>  
    </div>    
</div>