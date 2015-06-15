<?php
require_once("init.inc.php");
App::LoadModuleClass("Loyalty", "Rewards");
App::LoadControl("Button");

$_Rewards = new Rewards();

$fproc = new FormsProcessor();
$viewbutton = new Button("viewbutton", "viewbutton", "View More");
$viewbutton->CssClass = "btnDefault roundedcorners";
$viewbutton->Args = "onlick='button();' ";
$fproc->AddControl($viewbutton);

$arrRewards = $_Rewards->getRewardItems();
foreach ($arrRewards as $item) {
    $items['path'] = $item['RewardItemImagePath'];
    $items['id'] = $item['RewardItemID'];
    $path[] = $items;
}
?> 
<?php include('header.php'); ?>
<link href="js/imagejs/js-image-slider.css" rel="stylesheet" type="text/css" />
<script src="js/imagejs/js-image-slider.js" type="text/javascript"></script>
<link href="generic.css" rel="stylesheet" type="text/css" />
<script src="jquery.carouFredSel-6.0.4-packed.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="style.css">
<script type="text/javascript">
    $(document).ready(function() {
        $(".popup").click(function (e) {
            InitializeDialog($("#detailbox"), $(this).attr("href"));
            e.preventDefault();
            $("#detailbox").dialog("open");
        });
        function InitializeDialog($element, page) {
            $element.dialog({
                modal: true,
                autoOpen: true,
                width: 600,
                height:500,
                title: "Image Info",
                closeOnEscape: true,
                position: "center",
                buttons: {
                    Close: function() {
                    $( this ).dialog( "close" );
                    }
                },
                open: function (event, ui) {
                    $element.load(page);
                },
                close: function () {
                    $(this).dialog('close');
                }
            });
        }
        $('#carousel ul').carouFredSel({
            prev: '#prev',
            next: '#next',
            pagination: "#pager",
            scroll: 5000
        });
                
        $('#viewbutton').click(function(){                                   
            $("#sliderFrame").hide();   
            $('#wrapper').css('margin-left','+=950');    
            $('#wrapper').css('margin-top','+=150');
        });
        });
</script>
<div id="sliderFrame">
    <div id="slider">  
        <?php
        foreach ($path as $image) {
            echo "<img src =\"imagepath/" . $image['path'] . "\"/>";
        }
        ?>
    </div>
    <div class="group1-Wrapper">
        <a onclick="imageSlider.previous()" class="group1-Prev"></a>
        <a onclick="imageSlider.next()" class="group1-Next"></a>
    </div>
    <div id ="viewmore">
    <?php echo $viewbutton; ?>
</div>
</div>

<div id="wrapper">
    <div id="carousel">
        <ul>
            <?php
            foreach ($path as $image) {
                echo "<li><a class=\"popup\" href=\"imageinfo.php?PathID=" . $image['id'] . "\"><img src =\"imagepath/" . $image['path'] . "\" width='50' height='50' /><span> </span></a></li>";
            }
            ?>
        </ul>
        <div class="clearfix"></div>
        <a id="prev" class="prev" href="#">&lt;</a>
        <a id="next" class="next" href="#">&gt;</a>
        <div id="pager" class="pager"></div>
    </div>
</div>
<div id ="detailbox">
</div>
<?php include('footer.php'); ?>
