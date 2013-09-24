<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/idle.js');
Yii::app()->clientScript->registerScript("validation","
               jQuery(document).ready(function(){
				function setMessage(msg) {
					$('#ActivityList').append('<li>' + new Date().toTimeString() + ': ' + msg + '</li>');
				}
                var logouturl = $('#logout').val();
				var awayCallback = function() {
					$.ajax(
                    {
                        url: 'protected/components/AutoLogout.php',
                        type: 'post',
                        data: {
                            page: function() {
                                return 'logout';
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                        window.location.href = logouturl;
                        },
                    });
				};
				var awayBackCallback = function() {
					setMessage('back');
				};
				var hiddenCallback = function() {
					setMessage('User is not looking at page');
				};
				var visibleCallback = function(){
					setMessage('User started looking at page again')
				};
				
				var idle = new Idle({
					onHidden : hiddenCallback,
					onVisible : visibleCallback,
					onAway : awayCallback,
					onAwayBack : awayBackCallback,
					awayTimeout : $('#Timeout').val() //away with default value of the textbox
				}).start();

				$('#Timeout').keydown(function(e) {
					if(e.keyCode == 13) {

						var timeout = $(this).val();
						setMessage('Timeout changed to: ' + timeout);
						idle.setAwayTimeout(timeout);
					}
				})
			});
",CClientScript::POS_HEAD);

?>

<h1>Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h1>

<p>Congratulations! You have successfully created your Yii application.</p>

<p>You may change the content of this page by modifying the following two files:</p>
<ul>
	<li>View file: <code><?php echo __FILE__; ?></code></li>
	<li>Layout file: <code><?php echo $this->getLayoutFile('main'); ?></code></li>
</ul>

<p>For more details on how to further develop this application, please read
the <a href="http://www.yiiframework.com/doc/">documentation</a>.
Feel free to ask in the <a href="http://www.yiiframework.com/forum/">forum</a>,
should you have any questions.</p>

        <input id="Timeout" type="hidden" value="<?php echo Yii::app()->params->idletimelogout;;?>" />
        <input id="logout" type="hidden" value="<?php echo Yii::app()->params->autologouturl;;?>" />
		
