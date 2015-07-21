<html>
    <head>
    <script src="//cdn.shopify.com/s/assets/external/app.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://<?=$store_settings['app_url']?>/frontend/web/css/site.css">
    <script src="//code.jquery.com/jquery-1.11.2.min.js"></script>
    <script type="text/javascript">
          ShopifyApp.init({
            apiKey: "<?=$store_settings['api_key']?>",  // Expects: 32 character API key string like ff9b1d04414785029e066f8fd0465d00
            shopOrigin: "https://<?=$user_settings['store_name']?>",  // Expects: https://exampleshop.myshopify.com
            debug: true
          });
    </script>
    <script type="text/javascript">
            window.mainPageTitle = "Settings";
            ShopifyApp.ready(function(){
                  ShopifyApp.Bar.initialize({
                    title: window.mainPageTitle,
                    icon: "https://<?=$store_settings['app_url']?>/frontend/web/favicon.ico"
                  });
            });
            
            document.domain = 'apps.opsway.com';
            
            function hideMessages()
            {
                $('.alert-success').css('display','none');
                $('.alert-success').css('opacity','0');
                $('.alert-danger').css('display','none');
                $('.alert-danger').css('opacity','0');
                $('.alert-warning').css('display','none');
                $('.alert-warning').css('opacity','0');
            }
            
            function showMessage(type,text)
            {
                $('.alert-' + type).css('display','block');
                if(text != undefined)
                    $('.alert-' + type).find('.message').html(text);
                 $('.alert-' + type).animate({
                    opacity :   1,
                },2000);
            }
            
            $(function(){
                $('.sync').on('click',function(){
                    var that = this;
                    $(that).addClass('disabled');
                    $('.save').addClass('disabled');
                    $(that).text('Synchronizing...');           
                    hideMessages();
                    showMessage('warning')
                    $.ajax({
                      'type'  :   'POST',
                      'url'   :   'https://<?=$store_settings['app_url']?>/frontend/web/index.php?r=pipeliner/getdata',
                      'data'  :   {'store' : '<?=$user_settings['store_name']?>'},
                      'success'   : function(data)
                      {      
                            hideMessages();
                            var result = $.parseJSON(data);
							var message = "";
                            if(result.success == true)
                            {
								console.info(result.success);
                                showMessage('success','<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">OK!</span> Data was synchonized successful');
                            } else
                            {
                                $.each(result.errors,function(key,value){
                                    if(message != "")
                                        message += "<br />";
                                    message += '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>' + value;
                                })
                                showMessage('danger',message);
                            }
                            $(that).removeClass('disabled');
                            $(that).text('Get Pipeliner data');
                            $('.save').removeClass('disabled');
                      }
                  });
                });
                
                $('.save').on('click',function(){
                    var that = this;
                    $(that).addClass('disabled');
                    $(that).text('Loading...');
                    hideMessages();
                    $.ajax({
                        'type'  :   'POST',
                        'url'   :   'https://<?=$store_settings['app_url']?>/frontend/web/index.php?r=site/saveconfig',
                        'data'  :   {'store' : '<?=$user_settings['store_name']?>','formData' : $('#formData').serializeArray()},
                        'success'   : function(data)
                        {
                            var result = $.parseJSON(data);
                            var message = "";
                            if(result.success == 'true')
                            {
                                showMessage('success');
                                if($('.sync').hasClass('disabled'))
                                {
                                    $('.sync').removeClass('disabled');
                                }
                            } else
                            {
                                $.each(result.errors,function(key,value){
                                    if(message != "")
                                        message += "<br />";
                                    message += '<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span><span class="sr-only">Error:</span>' + value;
                                })
                                showMessage('danger',message);
                            }
                            $(that).removeClass('disabled');
                            $(that).text('Save');
                        }
                    });
                });
            });
          </script>
    </head>
    <body>
        <div class="notification-container">
            <div class="alert alert-danger" role="alert" style="display:none;opacity:0;">
              <div class="message">
                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                <span class="sr-only">Error:</span>
                Enter a valid data
              </div>
            </div>  
            <div class="alert alert-warning" role="alert" style="display:none;opacity:0;">
              <div class="message">
                <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                <span class="sr-only">Warning:</span>
                Please wait while the data is synchronized
              </div>
            </div>  
            <div class="alert alert-success" role="alert" style="display:none;opacity:0;">
                <div class="message">
                  <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
                  <span class="sr-only">OK!</span>
                    Data was saved successful
                </div> 
            </div> 
        </div>
        <div class="page-header clearfix">
            <h1>API settings</h1>
            <div class="page-description"></div>
            <button class="btn btn-shopify green sync" <?php
                if(empty($user_settings['service_url']) || 
                    empty($user_settings['team_id']) || 
                    empty($user_settings['api_password']) || empty($user_settings['api_token'])
                  )
                        echo 'style="display: none"';
            ?>">Get Pipeliner data</button>
            <button class="btn btn-shopify green save"><i class="glyphicon glyphicon-ok"></i> Save</button>
        </div>
        <div class="section section-dashboard">
            <form id="formData">
                <div class="form-group">
                  <label for="exampleInputEmail1">Service URL</label>
                  <input type="text" class="form-control" name="service_url" id="service_url" placeholder="Enter service url" value="<?=(empty($user_settings['service_url']) ? "" : $user_settings['service_url'])?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputPassword1">Team ID</label>
                  <input type="text" class="form-control" name="team_id" id="team_id" placeholder="Enter Team ID" value="<?=(empty($user_settings['team_id']) ? "" : $user_settings['team_id'])?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputPassword1">API Password</label>
                  <input type="text" class="form-control" name="api_password" id="api_password" placeholder="Enter API Password" value="<?=(empty($user_settings['api_password']) ? "" : $user_settings['api_password'])?>">
                </div>
                <div class="form-group">
                  <label for="exampleInputPassword1">API Token</label>
                  <input type="text" class="form-control" name="api_token" id="api_token" placeholder="Enter API Token" value="<?=(empty($user_settings['api_token']) ? "" : $user_settings['api_token'])?>">
                </div>
          </form>
      </div>
    </body>
</html>