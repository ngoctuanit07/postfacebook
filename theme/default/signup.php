<!DOCTYPE html>
<html >
  <head>
  
    <meta charset="UTF-8">

    <title><?php echo Options::get("sitename") ." | ". lang("SIGNIN"); ?></title>

  	<link href="{{templateFolder}}/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  	<link href="{{templateFolder}}/css/signin_up.css" rel="stylesheet" type="text/css" />

  	<script src="{{templateFolder}}/js/jquery.js"></script>
  	<script src="{{templateFolder}}/bootstrap/js/bootstrap.min.js"></script>
  	<script src="{{templateFolder}}/js/signin_up.js"></script>

  </head>
  <body>

    <div class="logmod">
      <span class="logmod__logo">
        <img src="{{templateFolder}}/images/logo_large.png" alt="logo"/>
      </span>
    <div class="logmod__wrapper">

    <div class="logmod__container">
      <ul class="logmod__tabs">
        <li data-tabtar="lgm-1"><a href="signin.php"><?php echo lang("SIGNIN"); ?></a></li>
        <li data-tabtar="lgm-2" class="current"><a href="signup.php"><?php echo lang("SIGNUP"); ?></a></li>
      </ul>
      <div class="logmod__tab-wrapper">
        <!-- Sign up -->
        <div class="logmod__tab lgm-2 show">
          <div class="logmod__heading">
            <span class="logmod__heading-subtitle"><?php echo lang("ENTER_DETAILS_TO_CREATE_ACCOUNT"); ?></span>
            <?php 
            if(Input::Get("signup")){
              $validate = new Validate();
              $validation = $validate->check($_POST, array(
                'username' => array(
                  'disp_text' => lang("USERNAME"),
                  'required' => true,
                  'min' => 2,
                  'max' => 32,
                  'unique' => 'users',
                  'regex' => '/^[a-z0-9]+$/'
                  ),
                'password' => array(
                  'disp_text' => lang("PASSWORD"),
                  'required' => true,
                  'min' => 6,
                  'max' => 16
                  ),
                'repassword' => array(
                  'disp_text' => lang("RE_ENTER_PASSWORD"),
                  'required' => true,
                  'matches' => 'password'
                  ),
                'email' => array(
                  'disp_text' => lang("EMAIL"),
                  'required' => true,
                  'unique' => 'users',
                  'valid_email' => true
                  ),
                ));

              if($validation->passed()){
                $user = new User();
                $salt = Hash::salt(32);
                try{
                    // Activation code
                    $code = Token::generate();
                    
                    // Account activation
                    $active = Options::Get('users_must_confirm_email') == "1" ? 0 : 1;
                    
                    $user->create(array(
                      'username' => Input::get('username'),
                      'password' => Hash::make(Input::get('password'), $salt),
                      'salt' => $salt,
                      'email' => Input::get('email'),
                      'roles' => '2',
                      'act_code' => $code,
                      'active' => $active,
                      'signup' => date('Y-m-d H:i:s')
                    ));
                    
                    // If the user is successfully registered
                    if($user->find(Input::get('username'))){
                        
                        // Send confirmation email
                        if($active == 0){
                          
                          $registerMessage = "Hello ".Input::get('username').",<br /><br />";
                          $registerMessage .= "Thank you for registering with ".Options::get("sitename")."! To complete your registration, please click on the link below or paste it into a browser to confirm your e-mail address.<br/>";
                          
                          $registerMessage .= "<a href='".Options::Get("siteurl")."/confirmregistration.php?email=".Input::Get("email")."&code=".$code."' >".Options::Get("siteurl")."/confirmregistration.php?email=".Input::Get("email")."&code=".$code."</a>";

                          $registerMessage .= "<br/><br/><br/>Please do not reply to this message.<br/>".Options::get("sitename");

                          Mail::Send(Input::Get("email"),Options::get("sitename").' Account activation',$registerMessage);
                        }
                        
                        $registerSuccessMsg = lang('THANK_YOU_REGISTERING');
                        
                        if($active == 0)
                          $registerSuccessMsg .= "<br/>".lang('THANK_YOU_REGISTERING_CONFIRMATION');
                        
                        // User message after the signing up
                        Session::flash("signin","success",$registerSuccessMsg,true);
                        Redirect::To("signin.php");

                    }

                }catch(Exception $e){
                  echo "<div class='alert alert-danger alert-singnin' role='alert'>";
                  echo lang("OPERATION_FAILED_TRY_AGAIN")."<br/> ".$e->GetMessage();
                  echo "</div>";
                }
              }else{
                echo "<div class='alert alert-danger alert-singnin' role='alert'><ul>";
                foreach($validation->errors() as $error){
                  echo "<li>".$error."</li>";
                }
                echo "</ul></div>";
              }
          }
            ?>
          </div>
          <div class="logmod__form">
            <form accept-charset="utf-8" method="POST" action="#" class="simform signup">
              
              <div class="sminputs">
                <div class="input full">
                  <label class="string optional" for="username">
                    <?php echo lang("USERNAME"); ?>*
                  </label>
                  <input class="string optional" maxlength="255" name="username" id="username" placeholder="<?php echo lang("USERNAME"); ?>" type="text" size="50" value="<?php echo escape(Input::Get("username")); ?>" />
                </div>
              </div>

              <div class="sminputs">

                <div class="input string optional">
                  <label class="string optional" for="password">
                    <?php echo lang('PASSWORD'); ?>*
                  </label>
                  <input class="string optional" maxlength="255" name="password" id="password" placeholder="<?php echo lang('PASSWORD'); ?>" type="password" size="50" />
                </div>

                <div class="input string optional">
                  <label class="string optional" for="repassword">
                    <?php echo lang('RE_ENTER_PASSWORD'); ?>*
                  </label>
                  <input class="string optional" maxlength="255" name="repassword" id="repassword" placeholder="<?php echo lang('RE_ENTER_PASSWORD'); ?>" type="password" size="50" />
                </div>

              </div>

              <div class="sminputs">
                <div class="input full">
                  <label class="string optional" for="email">
                    <?php echo lang("EMAIL"); ?>*
                  </label>
                  <input class="string optional" maxlength="255" name="email" id="email" placeholder="<?php echo lang("EMAIL"); ?>" type="text" size="50" value="<?php echo escape(Input::Get("email")); ?>" />
                </div>
              </div>

              <div class="simform__actions">
                <input type="submit" class="submit" id="signupBtn" name="signup" value="<?php echo lang("CREATE_ACCOUNT"); ?>" />
                <span class="simform__actions-sidetext"><a class="special" role="link" href="recover.php"><?php echo lang("FORGOT_PASSWORD_USERNAME"); ?></a></span>
              </div> 
            </form>
          </div> 
          <div class="logmod__alter">
            <div class="logmod__alter-container">
              <p class="footer"><?php echo lang('COPYRIGHT'); ?></p>
            </div>
          </div>
        </div>

      </div>
    </div> 
  </div>
</div>
</body>
</html>