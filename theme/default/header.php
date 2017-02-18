<?php if (!defined('ABSPATH')) exit('No direct script access allowed'); 

$user = new User();
$fbaccount = new fbaccount();
$fbaccountDetails = $fbaccount->get($fbaccount->UserDefaultFbAccount());

?>
<html dir="<?php echo lang("DIR"); ?>">
<head>
	<title>{{title}} | <?php echo Options::get("sitename"); ?></title>
	<meta charset="UTF-8" />
	<meta name="description" content="">
	<meta name="author" content="Abdellah Gounane - Icodix.com">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
 	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<!-- CSS Files -->
	<link href="{{templateFolder}}/css/custom.css" rel="stylesheet" />
	<link href="{{templateFolder}}/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="{{templateFolder}}/css/jquery.datetimepicker.css" rel="stylesheet">
	<link href="{{templateFolder}}/css/datatables.bootstrap.min.css" rel="stylesheet">
	<link href="{{templateFolder}}/css/font-awesome.css" rel="stylesheet">

	<!-- JS Files -->
	<script src="{{templateFolder}}/js/jquery.js"></script>
	<script src="core/js/lang.js"></script>
	<script src="core/js/javascript.js"></script>
	<script src="{{templateFolder}}/js/jsui.js"></script>
	<script src="{{templateFolder}}/js/postpreview.js"></script>
	<script src="{{templateFolder}}/bootstrap/js/bootstrap.min.js"></script>
	<script src="{{templateFolder}}/js/jquery.datetimepicker.min.js"></script>
	<script src="{{templateFolder}}/js/jquery.dataTables.min.js"></script>
	<script src="{{templateFolder}}/js/dataTables.bootstrap.min.js"></script>

	<script>
	$(document).ready(function(){
			$('[data-toggle="tooltip"]').tooltip();
			jQuery('#scheduledPostTime').datetimepicker();
			
			$('#datatable').DataTable({
				"aaSorting": [],
		        "aoColumnDefs": [{
		            'bSortable': false,
		            'aTargets': [0]
		        }],
		        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
				"iDisplayLength": 50,
		    });

		    oTable = $('#groupsDatabale').DataTable({
				"aaSorting": [],
				"sDom": "<l<t>ip>",
		        "aoColumnDefs": [{
		            'bSortable': false,
		            'aTargets': [0]
		        }],
		        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
				"iDisplayLength": 50,
		    });

			$('#datatableSearchField').keyup(function(){
			      oTable.search($(this).val()).draw() ;
			})

	});
	</script>
</head>
<body>
<noscript>
<div class="alerts alert alert-danger">
	<span class="glyphicon glyphicon-warning-sign"></span>
	<p class='alerttext'>JavaScript MUST be enabled in order for you to use kingposter. However, it seems JavaScript is either disabled or not supported by your browser. If your browser supports JavaScript, Please enable JavaScript by changing your browser options, then try again.</p></div>
</noscript>
<div class='alerts'></div>
<nav class="navbar navbar-inverse" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
		<div class="logo"><a href="index.php" title="Home"><img src="theme/default/images/logo.png" alt="logo"></a></div>
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
		  <span class="sr-only">Toggle navigation</span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		  <span class="icon-bar"></span>
		</button>
    </div>
    <div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li><a href='index.php'><span class="glyphicon glyphicon-home"></span> <?php echo lang("HOME"); ?> </a></li>
				<li><a href='settings.php'><span class="glyphicon glyphicon-cog"></span> <?php echo lang("SETTINGS"); ?> </a></li>
				
				<li class="dropdown">
					<a href='settings.php' class="dropdown-toggle" data-toggle="dropdown" >
						<span class="glyphicon glyphicon-folder-open"></span>&nbsp;
						<?php echo lang("POSTS"); ?> 
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a href="posts.php">
							<span class="glyphicon glyphicon-duplicate"></span> 
							<?php echo lang("SAVED_POSTS"); ?>
						</a>
						</li>
						<li>
							<a href="scheduledposts.php">
							<span class="glyphicon glyphicon-time"></span> 
							<?php echo lang("SCHEDULED_POSTS"); ?> 
							</a>
						</li>
						<li role="separator" class="divider"></li>
						<li>
							<a href="logs.php">
							<span class="glyphicon glyphicon-alert"></span> 
							<?php echo lang("LOGS"); ?> 
							</a>
						</li>
					</ul>
				</li>
				
				<?php if($user->HasPermission("admin")){ ?>
						<li><a href='users.php'><span class="glyphicon glyphicon-user"></span> <?php echo lang("USERS"); ?> </a></li>
				<?php } ?>

				<li class="dropdown">
					<a href='settings.php' class="dropdown-toggle" data-toggle="dropdown" >
						<i class="fa fa-facebook"></i>&nbsp;
						&nbsp;Switch fb account
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu">
						<?php 
							if(count($fbaccount->getAll())){
					
								foreach($fbaccount->getAll() as $fba){
		  							echo "<li><a href='settings.php?switchFbAccount=".$fba->getFbId()."'>
		  								<img src='http://graph.facebook.com/".$fba->getFbId()."/picture?redirect=1&height=32&width=32&type=normal' style='vertical-align:middle;' width='32px' height='32px' onerror=\"this.src = 'theme/default/images/facebookUser.jpg'\"/>
		  							".$fba->getFirstname()." ".$fba->getLastname()."</a></li>";
								}

						 	}else{
						 		echo "<li><a href='#'>No facebook account available</a></li>";
						 	}

						?>
					</ul>
				</li>

				<?php if(defined('UPDATE')) { ?>
					<li><a href='http://goo.gl/RrrjcV' target="_blank"><span class="glyphicon glyphicon-ok-circle"></span> New update available!</a></li>
				<?php } ?>

			</ul>
			
			<ul class="nav navbar-nav navbar-right">
		        <li class="dropdown">
		          <a href="#" class="dropdown-toggle UserProfil" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
								<img src='http://graph.facebook.com/<?php echo $fbaccount->UserDefaultFbAccount(); ?>/picture?redirect=1&height=32&width=32&type=normal' style='vertical-align:middle;'  onerror="this.src = 'theme/default/images/facebookUser.jpg'"/>
								<span class="userFullName"><?php echo ucfirst($fbaccountDetails->getLastname())." ".ucfirst($fbaccountDetails->getFirstname()); ?></span>
							</a>
		          <ul class="dropdown-menu">
		            <li>
	            		<a href='settings.php'>
	            			<span class="glyphicon glyphicon-cog"></span>
	            			<?php echo lang("SETTINGS"); ?> 
	            		</a>
	            	</li>
					<li>
						<a href='#' onclick="window.open('resetaccesstoken.php','','height=570,width=600');">
							<span class='glyphicon glyphicon-repeat'></span> 
							<?php echo lang("RESET_ACCESS_TOKEN"); ?>
						</a>
					</li>
					<li role="separator" class="divider"></li>
					<li>
						<a href='logout.php'>
							<span class="glyphicon glyphicon-log-out"></span> 
							<?php echo lang("LOGOUT"); ?> 
						</a>
					</li>
		          </ul>
		        </li>
      </ul>
    </div>
  </div>
</nav>
<div id="wrapper">