<?php  
include('core/init.php');


$ScheduledPost = new ScheduledPosts();
$posts = new Posts();
$fb = new Facebook();
$fb_account = new FbAccount();
$template = new Template();

if(Input::get("action","GET") == "delete" && Input::Get("id","GET")){
	try{
		$ScheduledPost->delete(Input::Get("id","GET"));
		Session::Flash("scheduledPosts","success","The scheduled post(s)s has been deleted successfully",true);

	}catch(Exception $ex){
		Session::Flash("scheduledPosts","<script>alertBox('".$ex->GetMessage()."','danger','.messageBox');</script>");
	}
	
	Redirect::To("scheduledposts.php");
}

if(Input::get("action","GET") == "repeat" && Input::Get("id","GET")){
	try{
		DB::GetInstance()->Update("scheduledposts","id",Input::Get("id","GET"),array(
			"next_target" => '0',
			"status" => '0',
		));
	}catch(Exception $ex){
		Session::Flash("scheduledPosts","danger",$ex->GetMessage(),true);
	}
	Redirect::To("scheduledposts.php");
}


if(Input::get("action","GET") == "pause" && Input::Get("id","GET")){
	$stat = Input::Get("stat","GET") == "" ? "0" : Input::Get("stat","GET");
	try{
		DB::GetInstance()->Update("scheduledposts","id",Input::Get("id","GET"),array("pause" => $stat));
	}catch(Exception $ex){
		Session::Flash("scheduledPosts","danger",$ex->GetMessage(),true);
	}
	Redirect::To("scheduledposts.php");
}

$template->header("Scheduled posts");

if(Session::exists('scheduledPosts')){
	foreach(Session::Flash('scheduledPosts') as $error){
		echo "<div class='alert alert-".$error['type']."' role='alert'>";
		echo "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
		echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
		echo "&nbsp;".$error['message'];
		echo "</div>";
	}
}
					
?>
<div class="messageBox"></div>
<div class="panel panel-default">
	<div class="panel-heading">
		<h3 class="panel-title"><span class="glyphicon glyphicon-time"></span> <?php echo lang("SCHEDULED_POSTS"); ?> </h3>
	</div>
	<div class="panel-body">
		<table class="table table-bordered table-striped" id="datatable">
			<thead>
				<tr>
					<td>
						<input type='checkbox' id="checkbox-all" class="check-all checkbox-style" name='a' />
						<label for="checkbox-all"></label>
					</td>
					<td><?php echo lang("NEXT_POSTING_TIME"); ?></td>
					<td><?php echo lang("POST_INTERVAL"); ?></td>
					<td><?php echo lang("POST"); ?></td>
					<td><?php echo lang("FB_APP"); ?></td>
					<td><?php echo lang("FB_ACCOUNT"); ?></td>
					<td><?php echo lang("PAUSE_RESUME"); ?></td>
					<td><?php echo lang('STATUS'); ?></td>
					<td></td>
				</tr>
			</thead>
			<?php 
				try{
					$ScheduledPosts = $ScheduledPost->userPosts();
				}catch(Exception $e){
					echo "Error : ".$e->GetMessage();
				}
				
				if($ScheduledPosts){
					foreach($ScheduledPosts as $ScheduledPost){

						$postTitle = "<span style='color:red'>Not found!</span>";
						$app_name = "<span style='color:red'>Not found!</span>";
						
						if($posts->get($ScheduledPost->post_id)){
							$postTitle = $posts->get($ScheduledPost->post_id)->post_title;
						}
						
						if(isset(Facebook::App($ScheduledPost->post_app)->app_name)){
							$app_name = Facebook::App($ScheduledPost->post_app)->app_name;
						}
						
						$totalGroups = count(json_decode($ScheduledPost->targets,true));
						
						$status = $ScheduledPost->status == "1" ? "<span class='btn btn-success'>".lang('COMPLETED')." (".$totalGroups."/".$totalGroups.")</span>" : "<span class='btn btn-default'>Progress ".$ScheduledPost->next_target ."/".$totalGroups."</span>";
						$pause = $ScheduledPost->pause == "0" ? lang('PAUSE') : lang('RESUME');
						$stat = $ScheduledPost->pause == "0" ? "1" : "0";
						$pauseBtn = $ScheduledPost->pause == "0" ? "primary" : "warning";
						$pauseBtnIcon = $ScheduledPost->pause == "0" ? "pause" : "play";
						$fba = $fb_account->get($ScheduledPost->fb_account);
						echo "<tr>
						<td>
							<input type='checkbox' class='checkbox checkbox-style' name='' id='' value='' />
							<label for=''></label>
						</td>
						<td>".$ScheduledPost->next_post_time."</td>
						<td>".$ScheduledPost->post_interval." Min</td>
						<td>".$postTitle."</td>
						<td>".$app_name."</td>
						<td>".$fba->getFirstname()." ".$fba->getLastname()."</td>
						<td>
							<a href='scheduledposts.php?action=pause&stat=".$stat."&id=".$ScheduledPost->id."' class='btn btn-".$pauseBtn."'><span class='glyphicon glyphicon-".$pauseBtnIcon."'></span> ".$pause."
							</a></td>
						<td>".$status."</td>
						<td>
							<a href='scheduledposts.php?action=delete&id=".$ScheduledPost->id."' title='".lang('DELETE')."' class='btn btn-danger delete' id='".$ScheduledPost->id."' onclick='return confirm(\"".lang('SCHEDULE_DELETE_CONFIRM')."\");'><span class='glyphicon glyphicon-trash'></span></a>
							<a href='logs.php?scheduledpostid=".$ScheduledPost->id."' title='".lang('VIEW_LOG')."' class='btn btn-primary'><span class='glyphicon glyphicon-folder-open'></span></a>
							<a href='scheduledposts.php?action=repeat&id=".$ScheduledPost->id."' title='".lang('REPOST')."' class='btn btn-primary'><span class='glyphicon glyphicon-repeat'></span></a>
						</td>
						</tr>"
						;
					}
				}
				
			?>
		</table>
	</div>
</div>
<?php $template->footer(); ?>
