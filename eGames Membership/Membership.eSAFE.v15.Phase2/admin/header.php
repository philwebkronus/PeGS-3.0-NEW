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
$javascripts[] = "js/jquery.dropdown.js";
$javascripts[] = "js/hoverIntent.js";
$javascripts[] = "js/checkinput.js";
$stylesheets[] = "css/paging.css";
$stylesheets[] = "css/ie.css";
$stylesheets[] = "css/style.css";
$stylesheets[] = "css/datagrid.css";
$stylesheets[] = "css/validationEngine.jquery.css";
$stylesheets[] = "css/jquery-ui.css";
$stylesheets[] = "css/themedstyle.css";

$headerinfo = "";
if (isset($javascripts) && count($javascripts) > 0)
{
    for ($i = 0; $i < count($javascripts); $i++)
    {
        $js = $javascripts[$i];
        $headerinfo .= "<script language='javascript' type='text/javascript' src='$js' media='screen, projection'></script>\r\n";
    }
}
if (isset($stylesheets) && count($stylesheets) > 0)
{
    for ($i = 0; $i < count($stylesheets); $i++)
    {
        $css = $stylesheets[$i];
        $headerinfo .= "<link rel='stylesheet' type='text/css' media='screen' href='$css' />\r\n";
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
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <title><?php echo $pagetitle; ?></title>
        <?php echo $headerinfo; ?>
        <!--[if lte IE 7]>
            <link rel="stylesheet" type="text/css" href="css/ie.css" media="screen" />
        <![endif]-->
        <script language="javascript" type="text/javascript">
            $(document).ready(function() {
                getBrowserName();
                
            function getBrowserName()
            {
              if(navigator.appName=="Microsoft Internet Explorer") {
                   $('[placeholder]').focus(function() {
                      var input = $(this);
                      if (input.val() == input.attr('placeholder')) {
                        input.val('');
                        input.removeClass('placeholder');
                      }
                    }).blur(function() {
                      var input = $(this);
                      if (input.val() == '' || input.val() == input.attr('placeholder')) {
                        input.addClass('placeholder');
                        input.val(input.attr('placeholder'));
                      }
                    }).blur().parents('form').submit(function() {
                      $(this).find('[placeholder]').each(function() {
                        var input = $(this);
                        if (input.val() == input.attr('placeholder')) {
                          input.val('');
                        }
                      })
                    });
            }
          }
          
          
                $("#MainForm").validationEngine();
                $("#updatestatus").validationEngine();
            });
            
//            window.setTimeout("fadeErrorMsg();", 5000);
//            
//            function fadeErrorMsg() {
//                $("#errormessage").fadeOut('slow');
//            }

        function preventBackandForward()
        {
            window.history.forward();
        }
        
        preventBackandForward();
        window.inhibited_load=preventBackandForward;
        window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
        window.inhibited_unload=function(){void(0);};
        </script>
    </head>
    <body>        
        <form enctype="multipart/form-data" action="" method="post" name="MainForm" id="MainForm" <?php echo $autocompletestring; ?> >
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
                <div id="errormessage" title="Error Message" style="text-align: center; background-color: #FF0000; color: #FFFFFF; padding: 5px 5px 5px 5px; font-size: 12pt;">
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
