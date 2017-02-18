<?php  
include('core/init.php');

$template = new Template();

$logs = new Logs();

if(Input::get("action","GET") == "clear"){
	try{
		Logs::Clear();
		Session::Flash("logs","success",lang('LOGS_CLEARED'),true);
	}catch(Exception $ex){
		Session::Flash("logs","danger",$ex->GetMessage(),true);
	}
	
	Redirect::To("logs.php");
}

$template->header("Logs");

if(Session::exists('logs')){
	foreach(Session::Flash('logs') as $error){
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
		<h3 class="panel-title"><span class="glyphicon glyphicon-alert"></span> <?php echo lang("logs"); ?> </h3>
	</div>
	<div class="panel-body">
		<a href="logs.php?action=clear" title="Clear logs" class="btn btn-danger"><?php echo lang('CLEAR_LOGS'); ?></a>
		<table class="table table-bordered table-striped" id="datatable">
			<thead>
				<tr>
					<td width="150"><?php echo lang('DATE_CREATED'); ?></td>
					<td><?php echo lang('LOG_DETAILS'); ?></td>
				</tr>
			</thead>
			<tbody>
				<?php
				
					if(Input::get("scheduledpostid","GET")){
						$ScheduledPost = $logs->Get(Input::get("scheduledpostid","GET"));
					}else{
						$ScheduledPost = $logs->Get();
					}
					
					if($ScheduledPost){
						foreach($ScheduledPost as $log){
							echo "<tr>
							<td>".$log->date."</td>
							<td><p class='log'>".$log->content."</p></td>
							</tr>";
						}
					}
					
				?>
			</tbody>
		</table>
	</div>
</div>
			
<?php $template->footer(); ?>
