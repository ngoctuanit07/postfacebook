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
        <li data-tabtar="lgm-1" class="current"><a href="signin.php"><?php echo lang("SIGNIN"); ?></a></li>
        <?php if(Options::Get('users_can_register') != "0"): ?>
        <li data-tabtar="lgm-2"><a href="signup.php"><?php echo lang("SIGNUP"); ?></a></li>
        <?php endif; ?>
      </ul>
      <div class="logmod__tab-wrapper">
      <!-- Signin -->
      <div class="logmod__tab lgm-1 show">
        <div class="logmod__heading">
          <span class="logmod__heading-subtitle"><?php echo lang("ENTER_USERNAME_PASSWORD_TO_SIGN_IN"); ?></span>
          <?php 
            if(Session::exists('signin')){
              foreach(Session::Flash('signin') as $error){
                echo "<div class='alert alert-".$error['type']." alert-singnin' role='alert'>";
                echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
                echo "&nbsp;".$error['message'];
                echo "</div>";
              }
            }

            if(Input::Get("signin")){
                $validate = new Validate();
                $validation = $validate->check($_POST, array(
                  'username' => array(
                    'disp_text' => lang('USERNAME'),
                    'required' => true
                    ),
                  'password' => array(
                    'disp_text' => lang('PASSWORD'),
                    'required' => true
                    )
                  ));

                if($validation->passed()){
                  
                  $user = new User();

                  $remember = Input::get('remember') == "on" ? true : false;

                  try{
                    $login = $user->login(Input::get('username'), Input::get('password'),$remember);
                    Redirect::To("index.php");
                  }catch(Exception $ex){
                    
                    echo "<div class='alert alert-danger alert-singnin' role='alert'>";
                    echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
                    echo "&nbsp;".$ex->GetMessage();
                    echo "</div>";

                  }
                  
                }else{
                   echo "<div class='alert alert-danger alert-singnin' role='alert'>";
                    echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
                    echo "&nbsp;".lang("ENTER_USERNAME_PASSWORD");
                    echo "</div>";
                }
            }

          ?>
        </div> 
        <div class="logmod__form">
          <form accept-charset="utf-8" action="#" method="POST" class="simform signin">
            <div class="sminputs">
              <div class="input full">
                <label class="string optional" for="username"><?php echo lang("USERNAME"); ?>*</label>
                <input class="string optional" name="username" maxlength="255" id="username" placeholder="<?php echo lang("USERNAME"); ?>" type="text" size="50" />
              </div>
            </div>
            <div class="sminputs">
              <div class="input full">
                <label class="string optional" for="password"><?php echo lang("PASSWORD"); ?>*</label>
                <input class="string optional" maxlength="255" name="password" id="password"  placeholder="<?php echo lang("PASSWORD"); ?>" type="password" size="50" />
                <span class="hide-password"><?php echo lang("SHOW"); ?></span>
              </div>
            </div>
            <div class="simform__actions">
              
              <input type="submit" class="submit" name="signin" id="signinBtn" value="<?php echo lang("SIGNIN"); ?>" />
              
              <span class="simform__actions-sidetext">
                <input type="checkbox" name="remember" id="remember" class="checkbox-style"/>&nbsp;
                <label for="remember"></label>
                <span class="checkboxText" ><?php echo lang("REMEMBER_ME"); ?></span>
              </span>
              
              <span class="simform__actions-sidetext">
                <a class="special" role="link" href="recover.php"><?php echo lang("FORGOT_PASSWORD_USERNAME"); ?></a>
              </span>

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