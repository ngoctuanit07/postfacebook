<?php  
include('core/init.php');

$posts = new Posts();
$template = new Template();
					
if(Input::get("action","GET") == "delete" && Input::Get("id","GET")){
	try{
		$posts->delete(Input::Get("id","GET"));
		Session::Flash("posts","success",lang('POST_DELETED_SUCCESS'),true);
	}catch(Exception $ex){
		Session::Flash("posts","danger",$ex->GetMessage(),true);
	}
	
	Redirect::To("posts.php");
}

$template->header("Posts");

if(Session::exists('posts')){
	foreach(Session::Flash('posts') as $error){
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
		<h3 class="panel-title"><span class="glyphicon glyphicon-duplicate"></span>  <?php echo lang("POSTS"); ?> </h3>
	</div>
	<div class="panel-body">
		<table class="table table-bordered table-striped" id="datatable">
			<thead>
				<tr>
					<td width="20px">
						<input type='checkbox' id="checkbox-all" class="check-all checkbox-style" name='a' />
						<label for="checkbox-all"></label>
					</td>
					<td><?php echo lang('POST_TITLE')?></td>
					<td><?php echo lang('POST_TYPE')?></td>
					<td><?php echo lang('DATE_CREATED')?></td>
					<td></td>
				</tr>
			</thead>
			<?php 
				try{
					$posts = new Posts();
					$posts = $posts->get();
				}catch(Exception $e){
					echo "Error : ".$e->GetMessage();
				}
				
				if($posts){

					$postIcons = array('message' => 'align-left','link' => 'link', 'image' => 'picture', 'video' => 'facetime-video' );
					foreach($posts as $post){
						echo "<tr>
						<td>
							<input type='checkbox' class='checkbox checkbox-style' name='' id='' value='' />
							<label for=''></label>
						</td>
						<td>".escape($post->post_title)."</td>
						<td><h4><span class='label label-default'><span class='glyphicon glyphicon-".$postIcons[Posts::PostType($post->content)]."'> ".ucfirst(Posts::PostType($post->content))."</span></span></h4></td>
						<td>".$post->date_created."</td>
						<td>
						<a href='posts.php?action=delete&id=".$post->id."' title='".lang('DELETE')."' class='btn btn-danger delete' id='".$post->id."' onclick='return confirm(\"".lang('POST_DELETE_CONFIRM')."\");'><span class='glyphicon glyphicon-trash'></span> ".lang('DELETE')."</a>
						<a href='index.php?post_id=".$post->id."' title='".lang('POST')."' class='btn btn-primary' id='".$post->id."'><span class='glyphicon glyphicon-pencil'></span> ".lang('POST')."</a>
						</td>
						</tr>";
					}
				}
				
			?>
		</table>
	</div>
</div>
			
<?php $template->footer(); ?>
