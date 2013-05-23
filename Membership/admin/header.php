<?php
/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */

$javascripts[] = "../js/jquery.min.js";
$javascripts[] = "../js/jquery-ui.min.js";
$javascripts[] = "../js/jquery.validationEngine.js";
$javascripts[] = "../js/jquery.validationEngine-en.js";
$stylesheets[] = "../css/ui.theme.css";
$stylesheets[] = "../css/jquery-ui.css";
$stylesheets[] = "../css/smoothness/jquery-ui-1.8.16.custom.css";
$stylesheets[] = "../css/validationEngine.jquery.css";
$stylesheets[] = "../css/styles.css";
$stylesheets[] = "../css/paging.css";


$headerinfo = "";
if (isset($javascripts) && count($javascripts) > 0)
{
    for ($i = 0; $i < count($javascripts); $i++)
    {
        $js = $javascripts[$i];
        $headerinfo .= "<script language='javascript' type='text/javascript' src='$js'></script>\r\n";
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
$pagetitle = (isset($pagetitle) && $pagetitle != "" ) ? $pagetitle : "Admin";
$curdate = new DateSelector('now', 'Y,m,d, H,i,s');
$curdate->SetTimeZone("Asia/Manila");
$timezone = $curdate->GetCurrentDateFormat('O (T)');
(isset($autocomplete) && $autocomplete == false) ? $autocompletestring = "autocomplete='off'" : $autocompletestring = "";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title><?php echo $pagetitle; ?></title>
        <?php echo $headerinfo; ?>
        <script language="javascript" type="text/javascript">
            $(document).ready(
            function() 
            {
                $("#menu li").click(function() {
                        //	First remove class "active" from currently active tab
                        //$("#menu li").removeClass('active');

                        //	Now add class "active" to the selected/clicked tab
                        //$(this).addClass("active");

                        //	Hide all tab content
                        //$(".tab_content").hide();

                        //	Here we get the href value of the selected tab
                        var selected_tab = $(this).find("a").attr("href");
                        
                        //	Show the selected tab content
                        //$(selected_tab).fadeIn();

                        //	At the end, we add return false so that the click on the link is not executed
                        return true;
                });
            });
            
            
        </script>
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