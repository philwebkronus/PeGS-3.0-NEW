<?php
/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */

$javascripts[] = "js/jquery.min.js";
$javascripts[] = "js/jquery-ui.min.js";
$javascripts[] = "js/jquery.validationEngine.js";
$javascripts[] = "js/jquery.validationEngine-en.js";
$javascripts[] = "js/tools.js";
//$javascripts[] = "js/slider/ad_gallery.js";
$javascripts[] = "js/slider/jquery.ad-gallery.js";

if (isset($customjavascripts) && count($customjavascripts) > 0)
{
    for ($i = 0; $i < count($customjavascripts); $i++)
    {
        $javascripts[] = $customjavascripts[$i];
    }
}

$stylesheets[] = "css/ui.theme.css";
$stylesheets[] = "css/jquery-ui.css";
$stylesheets[] = "css/smoothness/jquery-ui-1.8.16.custom.css";
$stylesheets[] = "css/validationEngine.jquery.css";
$stylesheets[] = "css/styles.css";
$headerinfo = "";
if (isset($javascripts) && count($javascripts) > 0)
{
    for ($i = 0; $i < count($javascripts); $i++)
    {
        $js = $javascripts[$i];
        $headerinfo .= "<script language='javascript' type='text/javascript' src='$js'  media='screen, projection'></script>\r\n";
    }
}
if (isset($stylesheets) && count($stylesheets) > 0)
{
    for ($i = 0; $i < count($stylesheets); $i++)
    {
        $css = $stylesheets[$i];
        $headerinfo .= "<link rel='stylesheet' type='text/css' href='$css' />\r\n";
    }
}

$pagetitle = (isset($pagetitle) && $pagetitle != "" ) ? $pagetitle : "Dashboard V2";
$curdate = new DateSelector('now', 'Y,m,d, H,i,s');
$curdate->SetTimeZone("Asia/Manila");
$timezone = $curdate->GetCurrentDateFormat('O (T)');
(isset($autocomplete) && $autocomplete == false) ? $autocompletestring = "autocomplete='off'" : $autocompletestring = "";


if (isset($useCustomHeader) && $useCustomHeader)
{
    App::LoadCore("File.class.php");
    $headerfile = "templates/headertemplate.php";
    $headerfp = new File($headerfile);
    $headerstring = trim($headerfp->ReadToEnd());
    $headerstring = str_replace("Page not found &laquo; PEGS Website", "Membership System", $headerstring);
    $arrheaders = explode("</head>", $headerstring);
    $arrbody = explode("<body>", $arrheaders[1]);
    echo $arrheaders[0];
}

if (isset($customtags) && count($customtags) > 0)
{
    for ($i = 0; $i < count($customtags); $i++)
    {
        $headerinfo .= $customtags[$i] . "\r\n";
    }
}

?>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $pagetitle; ?></title>
    <?php echo $headerinfo; ?> 
    <!--[if gt IE 5]>
                <link rel="stylesheet" type="text/css" href="css/ie.css" />
    <![endif]-->
    <!--[if gt IE 8]>
                <link rel="stylesheet" type="text/css" href="css/ie9.css" />
    <![endif]-->
    <script language="javascript" type="text/javascript">
        $(document).ready(function(){
            $("#MainForm").validationEngine();
        });
        
        function preventBackandForward()
        {
            window.history.forward();
        }
        
        preventBackandForward();
        window.inhibited_load=preventBackandForward;
        window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
        window.inhibited_unload=function(){void(0);};

    </script>
    <?php 
    if($useCustomHeader)
    {
        echo $arrbody[0]; 
        echo $arrbody[1];
    }
    ?>
</head>
<body>
<form action="" method="post" name="MainForm" id="MainForm" <?php echo $autocompletestring; ?> >
    <div id="dashboard-messagebox" title="Message Box" style="display: none;">
        <p>
            <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 50px 0;"></span>
        <div id="dashboard-messagebox-string">&nbsp;</div>
        </p>
    </div>
    <?php
    //App::pr(currenttime);
    if (App::HasError())
    {
        ?>
        <div id="errormessage" title="Error Message" style="background-color: #FF0000; color: #FFFFFF; padding: 5px 5px 5px 5px; font-size: 12pt;">
            <?php echo App::GetErrorMessage(); ?>
        </div>
        <?php
    }
    ?>
    <?php
    if (App::GetSuccessMessage())
    {
        ?>
        <div id="successmessage" title="Success Message" style="background-color: #CCCC33; color: #FFFFFF; padding: 5px 5px 5px 5px; font-size: 12pt;">
            <?php echo App::GetSuccessMessage(); ?>
        </div>
        <?php
    }
    ?>