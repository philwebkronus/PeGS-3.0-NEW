jQuery(document).ready(function(){
				function setMessage(msg) {
					$('#ActivityList').append('<li>' + new Date().toTimeString() + ': ' + msg + '</li>');
				}
                
                var timeout = $('#Timeout').val()
				var awayCallback = function() {
					$.ajax(
                    {
                        url: 'autoLogout',
                        type: 'post',
                        data: {
                            page: function() {
                                return 'logout';
                            }
                        },
                        datatype: 'json',
                        success: function(data)
                        {
                        $('#mydialog').dialog('open');
                        
                        }
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
					awayTimeout : timeout //away with default value of the textbox
				});

				$('#Timeout').keydown(function(e) {
					if(e.keyCode == 13) {

						var timeout = $(this).val();
						setMessage('Timeout changed to: ' + timeout);
						idle.setAwayTimeout(timeout);
					}
				})
			});