<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title><?php echo $this->title; ?></title>
        <link rel="stylesheet" type="text/css" media="screen" href="css/login.css" />
        <!--[if IE]>
            <link rel="stylesheet" href="css/ie-login.css" type="text/css" media="screen, projection">
        <![endif]-->      
        <link rel="stylesheet" type="text/css" media="screen" href="jscripts/fancybox/jquery.fancybox-1.3.4.css" />
<!--        <script type="text/javascript" src="https://getfirebug.com/firebug-lite.js"></script>-->
        <script type="text/javascript" src="jscripts/jquery.min.js"></script>
        <script type="text/javascript" src="jscripts/login.js"></script>
        <script type="text/javascript" src="jscripts/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
        <script type="text/javascript" src="jscripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        <script type="text/javascript" src="jscripts/jquery.lightbox.js"></script>        
    </head>
    <body>
        <?php echo $content; ?>
    <script type="text/javascript">
    $(document).ready(function(){
        <?php if(!isIEBrowser()): ?>
            $.fancybox("Please use Internet Explorer",{modal:true});
        <?php endif; ?>
    });
    </script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-7240274-33']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body>
</html>


