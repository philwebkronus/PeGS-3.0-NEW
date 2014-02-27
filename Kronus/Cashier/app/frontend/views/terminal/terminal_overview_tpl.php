<div class="clear"></div>
<div id="temrinalmonitoring-pager">
    Terminals: <?php echo TerminalMonitoringPager::display(($total_terminal / 2), Mirage::app()->param['terminal_per_page'] * 2,'terminal/overview'); ?>
</div>
<table id="tblterminalmonitoring" border="1">
    <?php $cntr = 0; ?>
    <tr>
    <?php foreach($terminals as $key => $terminal): ?>
        <?php //debug($terminal); exit; ?>
        <?php if($cntr != 0 && $cntr % 10 == 0 ): ?>
        </tr><tr>
        <?php endif; ?>
        <?php if($cntr % 2 == 0): ?>  
            <?php $checkbox = ''; $button = ''; $casino = ''; $last_balance = ''; $asof = ''; ?>
            <?php $code = $terminal['tc']; // always regular terminal code ?>
            
            <?php $tcode = $terminal['tc']; // set default terminal code if not vip ?>
            
            
            <?php // IF VIP NOT EXIST BREAK ?>
            <?php if(!isset($terminals[$key + 1])): ?>
                <?php $class='disable'; ?>
            <?php if($terminal['TerminalType'] == 1){
                $code = 'G'.$code;
                $tcode = 'G'.$tcode;
            }
            ?>
            <td id="<?php echo $code ?>" class="<?php echo $class ?>">
                <div class="box tcode"><h1><?php echo $tcode ?></h1></div>
                <div class="box chk"><?php echo $checkbox; ?></div>
                <div class="clear"></div>
                <p class="casino_code"><?php echo $casino; ?></p>
                <p class="lastbalance"><?php echo $last_balance; ?><span class="asof"><?php echo $asof; ?></span></p>
                <?php echo $button; ?>
            </td> 
            <?php $cntr++; ?>
            <?php break; ?>
            <?php endif; ?>
            <?php $nonvipid = $terminal['TerminalID']; // terminal id non-vip ?>
            <?php $vipid = $terminals[$key + 1]['TerminalID']; // terminal id vip ?>
                <?php $casinovip = $services[$vipid]; $casinononvip = $services[$nonvipid]; ?>
            
            <?php // check if has last balance RED ?>
            <?php if((isset($terminals[$key + 1]) && $terminals[$key + 1]['lastbalance'] != null) || $terminal['lastbalance'] != null && ($terminals[$key + 1]['Status'] == 1 && $terminal['Status'] == 1)): ?>
                    <?php $last_balance = 'PhP '.$terminal['lastbalance']; ?>
                    <?php $casino = playingDuration($terminal['DateStarted']).' - '.((isset($refservices[$terminal['usedServiceID']]))?$refservices[$terminal['usedServiceID']]:'');  ?>
                    <?php $tid = $terminal['TerminalID']; ?>
                    <?php $cid = $terminal['usedServiceID']; ?>
                    <?php $asof = ' as of '.$terminal['ltd']; ?>
                    <?php // check if vip ?>
                    
                    <?php if($terminals[$key + 1]['lastbalance'] != null): ?>
                            <?php $tid = $terminals[$key + 1]['TerminalID']; ?>
                            <?php $tcode = $terminals[$key + 1]['tc']; ?>
                            <?php $cid = $terminals[$key + 1]['usedServiceID']; ?>
                            <?php $casino = playingDuration($terminals[$key + 1]['DateStarted']).' - '.$refservices[$terminals[$key + 1]['usedServiceID']]; ?>
                            <?php $last_balance = 'PhP '.$terminals[$key + 1]['lastbalance']; ?>
                            <?php $asof = ' as of '.$terminals[$key + 1]['ltd']; ?>
                    <?php endif; ?>
                    <?php $class = 'active'; ?>
                    <?php $button='<input type="button" tid="'.$tid.'" cid="'.$cid.'" value="Reload" class="reload"/><input tid="'.$tid.'" cid="'.$cid.'" type="button" value="Redeem" class="redeem"/>' ?>
            <?php // check if disabled GRAY ?>    
            <?php elseif($terminals[$key + 1]['Status'] == 2 || $terminal['Status'] == 2 || $terminals[$key + 1]['Status'] == 0 || $terminal['Status'] == 0 || $terminals[$key + 1]['Status'] == 3 || $terminal['Status'] == 3): ?>
                <?php $class='disable'; ?>
            <?php // not active GREEN ?>    
            <?php else: ?>    
                <?php $checkbox = '<p> vip <input class="togglevip" type="checkbox" /></p>'; ?>
                <?php $button = '<input type="button" value="START SESSION" class="startsession"/>'; ?>
                <?php $class = 'notactive'; ?>
                <?php $casino = $casinononvip; ?>
            <?php endif; ?>
            <?php if($terminal['TerminalType'] == 1){
                $code = 'G'.$code;
                $tcode = 'G'.$tcode;
            }
            ?>
            <td id="<?php echo $code ?>" casinononvip="<?php echo $casinononvip ?>" casinovip="<?php echo $casinovip ?>" vipid="<?php echo $vipid ?>" nonvipid="<?php echo $nonvipid ?>" class="<?php echo $class ?>">
                <div class="box tcode"><h1><?php echo $tcode ?></h1></div>
                <div class="box chk"><?php echo $checkbox; ?></div>
                <div class="clear"></div>
                <p class="casino_code"><?php echo $casino; ?></p>
                <p class="lastbalance"><?php echo $last_balance; ?><span class="asof"><?php echo $asof; ?></span></p>
                <?php echo $button; ?>
            </td>    
        <?php endif; ?>
    <?php $cntr++; ?>
    <?php endforeach; ?>
    </tr>    
</table>
<script type="text/javascript" src="jscripts/strtotime.js"></script>
<script type="text/javascript" src="jscripts/terminal-monitoring-pager.js"></script> 
<script type="text/javascript" src="jscripts/validation.js"></script>
<script type="text/javascript" src="jscripts/check_partner.js"></script>

<?php Mirage::loadLibraries(array('CardScripts','ButtonClickScripts','HotkeyScripts','RefreshScripts')); ?>

<script type="text/javascript">
jQuery(document).ready(function(){
    $('.togglevip').live('click',function() {
        var parentid = $(this).parents('td').attr('id');
        var casino_vip = $('#'+parentid).attr('casinovip');
        var casino_non_vip = $('#'+parentid).attr('casinononvip');
        if($(this).is(':checked')) {
            $('#'+parentid).children('.tcode').html('<h1>'+parentid + 'vip</h1>');
            $('#'+parentid).children('.casino_code').html(casino_vip);
        } else {
            $('#'+parentid).children('.tcode').html('<h1>'+parentid+'</h1>');
            $('#'+parentid).children('.casino_code').html(casino_non_vip);
        }
    });
    
    $('#get_info_card').live('click',function(){
        $.ajax({
            url : '<?php echo Mirage::app()->createUrl('terminal') ?>'
        });
        return false;
    });
    
});
</script>
<?php //Mirage::loadWidget('TerminalPanel') ?>