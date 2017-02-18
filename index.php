<?php
require "core/init.php";

$template = new template();
$fb = new Facebook();
$user = new User();
$fbaccount = new fbaccount();


if(!$fbaccount->UserDefaultFbAccount()){
	Session::Flash("home","warning",lang('NO_FB_ACCOUNT_AVAILABLE'),true);
}

$fbaccountDetails = $fbaccount->get($fbaccount->UserDefaultFbAccount());

if(Input::get('groupscategory')){
	Session::put("groupscategory",(int)Input::get('groupscategory'));
}

if(Input::get('addCategory')){
	if($res = $fbaccount->addGroupCategory(Input::get('newCategoryName'))){
		Session::Flash("home","danger",$res,true);
	}else{
		Session::Flash("home","success",lang('CATEGORY_ADDED_SUCCESSFULLY'),true);
	}
}

// Get default app
if(!$fbaccount->UserFbAccountDefaultApp()){
	Session::Flash("home","warning",lang('NO_APP_SELECTED'),true);
}

if(Input::get('removeGroup')){
	try{
		$fbaccount->removeGroupFromCategory(Input::get('removeGroup'));
		Session::Flash("home","success",lang('GROUP_RMOVED_SUCCESS'),true);
	}catch(Exeption $ex){
		Session::Flash("home","danger",$ex->getMessage(),true);
	}
	Redirect::to('index.php');
}

if(Input::get('deleteCategory')){
	try{
		$fbaccount->deleteCategory(Input::get('deleteCategory'));
		Session::Flash("home","success",lang('CATEGORY_DELETED_SUCCESS'),true);
	}catch(Exeption $ex){
		Session::Flash("home","danger",$ex->getMessage(),true);
	}
	Redirect::to('index.php');
}

// Get list of groups
$groups = $fbaccount->GetGroups();

// Get list of categories
$groupsCategories = $fbaccount->GetGroupCategories($fbaccount->UserDefaultFbAccount());

// Load post if the post id has been passed
if(Input::Get("post_id","GET")){
	$posts = new Posts();
	$getPost = $posts->get(Input::Get("post_id"));
	if($getPost){
		
		$post = json_decode($getPost->content);

		$_POST['postTitle'] = escape($getPost->post_title);
		$_POST['postId'] = $getPost->id;
		
		$_POST['message'] = escape($post->message);
		$_POST['postType'] = "message";
		
		// Set Post type
		if(Posts::PostType($getPost->content) == "link"){
			$_POST['postType'] = "link";
			$_POST['link'] = escape($post->link);
			$_POST['picture'] = escape($post->picture);
			$_POST['name'] = escape($post->name);
			$_POST['caption'] = escape($post->caption);
			$_POST['description'] = escape($post->description);
		}

		// Set Post type
		if(Posts::PostType($getPost->content) == "image"){
			$_POST['postType'] = "image";
			$_POST['image'] = escape($post->image);;
		}

		// Set Post type
		if(Posts::PostType($getPost->content) == "video"){
			$_POST['postType'] = "video";
			$_POST['video'] = escape($post->video);
			$_POST['description'] = isset($post->description) ? escape($post->description) : "";
		}
	}
}else{
	$_POST['postType'] = "message";
}

$template->header("Home");

if(Session::exists('home')){
	foreach(Session::Flash('home') as $error){
		echo "<div class='alert alert-".$error['type']."' role='alert'>";
		echo "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
		echo "<span class='glyphicon glyphicon-exclamation-sign' aria-hidden='true'></span>";
		echo "&nbsp;".$error['message'];
		echo "</div>";
	}
}

?>
<div class="homeMessageBox"></div>
<!-- Save post dialog -->
<div id="postTitleModal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"><?php echo lang("POST_TITLE"); ?></h4>
			</div>
			<div class="modal-body">
				<div class="messageBoxModal"></div>
				<div class="formField">
					<label for="postTitle"><?php echo lang("POST_TITLE"); ?></label>
					<input type="text" name='postTitle' id="postTitle" class="form-control" placeholder='<?php echo lang("POST_TITLE"); ?>.' value="<?php echo Input::Get("postTitle");?>" />
				</div>
			</div>
			<div class="modal-footer">
				<a class="btn btn-default" data-dismiss="modal"><?php echo lang("CLOSE"); ?></a>
				<a id="savePostModal" class="btn btn-primary"><?php echo lang("SAVE_POST"); ?></a>
			</div>
		</div>
	</div>
</div>
				
<form method='POST' action id="postForm" name="postForm">
<div class="row">
    <div class="col-sm-6">
      <div class="panel panel-default">
        <div class="panel-heading">
				<ul class="postType">

					<li>
					<a href="#" onclick="return false;" class="postTypeMessage <?php if(Input::Get("postType") == "message") echo "postTypeActive"; ?>"><span class="glyphicon glyphicon-align-left"></span> <?php echo lang("MESSAGE"); ?> </a>
					</li>

					<li>
					<a href="#" onclick="return false;"  class="postTypeLink <?php if(Input::Get("postType") == "link") echo "postTypeActive"; ?>">
					<span class="glyphicon glyphicon-link"></span> <?php echo lang("LINK");?> </a>
					</li>

					<li>
					<a href="#" onclick="return false;"  class="postTypeImage <?php if(Input::Get("postType") == "image") echo "postTypeActive"; ?>">
					<span class="glyphicon glyphicon-picture"></span> <?php echo lang("IMAGE");?> </a>
					</li>

					<li>
					<a href="#" onclick="return false;"  class="postTypeVideo <?php if(Input::Get("postType") == "video") echo "postTypeActive"; ?>">
					<span class="glyphicon glyphicon-facetime-video"></span> <?php echo lang("VIDEO");?> </a>
					</li>

				</ul>
			<div class="clear"></div>
        </div>
        <div class="panel-body">
		<input type="hidden" name="postType" id="postType" value="<?php echo Input::Get("postType"); ?>" />
		<input type="hidden" name="postId" id="postId" value="<?php echo Input::Get("postId");?>" />
			<div class="formField">
				<label for="message"><?php echo lang("MESSAGE"); ?> <a href="#"  onclick="return false;" data-toggle="tooltip" data-placement="top" style="float:right" title="Spinning example : {Hello|Howdy|Hola} to you, {Mr.|Mrs.|Ms.} {foo|bar|foobar}!!"><span class="glyphicon glyphicon-question-sign"></span></a></label>
				<textarea name='message' id="message" rows='3' cols='50' class="form-control" placeholder='Your status here...'><?php echo Input::Get("message");?></textarea>

				<div id="emoticons">
					<a href="#" title="(y)" class="emoji like"></a>
					<a href="#" title=":)" class="emoji smile"></a>
					<a href="#" title=":D" class="emoji grin"></a>
					<a href="#" title=":(" class="emoji frown"></a>
					<a href="#" title=":'(" class="emoji cry"></a>
					<a href="#" title=":p" class="emoji tongue"></a>
					<a href="#" title=":3" class="emoji colonthree"></a>
					<a href="#" title="O:)" class="emoji angel"></a>
					<a href="#" title="3:)" class="emoji devil"></a>
					<a href="#" title="<3" class="emoji heart"></a>
					<a href="#" title=":*" class="emoji kiss"></a>
					<a href="#" title="o.O" class="emoji confused"></a>
					<a href="#" title=";)" class="emoji wink"></a>
					<a href="#" title="8|" class="emoji sunglasses"></a>
					<a href="#" title="8-)" class="emoji glasses"></a>
					<a href="#" title=":v" class="emoji pacman"></a>
					<a href="#" title=":O" class="emoji gasp"></a>
					<a href="#" title="-_-" class="emoji squint"></a>
					<a href="#" title="^_^" class="emoji kiki"></a>
				</div>

			</div>

			<div id="postLinkDetails" <?php if(Input::Get("postType") != "link") echo "style='display:none'"; ?>>
				<div class="formField">
					<label for="link"><?php echo lang("LINK");?>
						<a href="#"  onclick="return false;" data-toggle="tooltip" data-placement="top" style="float:right" title="If you specifie any field below This field is required">
						<span class="glyphicon glyphicon-question-sign"></span></a>
					</label>
					<input type='text' name='link' class="form-control" id="link" value="<?php echo Input::Get("link");?>" placeholder='Post link here.' />
				</div>
				<div class="formField">
					<label for="picture"><?php echo lang("PICTURE"); ?></label>
					<input type='text' name='picture' id="picture" class="form-control"  value="<?php echo Input::Get("picture");?>" placeholder='Post picture here.' />
				</div>
				<div class="formField">
					<label for="name"><?php echo lang("NAME"); ?></label>
					<input type='text' id="name" name='name' class="form-control" value="<?php echo Input::Get("name");?>" placeholder='Post name here.' >
				</div>
				<div class="formField">
					<label for="caption"><?php echo lang("CAPTION"); ?></label>
					<input type='text' name='caption' id="caption" class="form-control" value="<?php echo Input::Get("caption");?>" placeholder='Post Caption here.' />
				</div>
				<div class="formField">
					<label for="description"><?php echo lang("DESCRIPTION"); ?></label>
					<textarea name='description' id="description" rows='3' cols='50' class="form-control" placeholder='Post description here.'><?php echo Input::Get("description");?></textarea>
				</div>
			</div>

			<div id="postImageDetails" <?php if(Input::Get("postType") != "image") echo "style='display:none'"; ?>>
				<div class="formField">
					<label for="image"><?php echo lang("IMAGE");?>
						<a href="#"  onclick="return false;" data-toggle="tooltip" data-placement="top" style="float:right" title="Image URL">
						<span class="glyphicon glyphicon-question-sign"></span></a>
					</label>
					<input type='text' name='image' class="form-control" id="image" value="<?php echo Input::Get("image");?>" placeholder='Image Link.' />
				</div>
			</div>

			<div id="postVideoDetails" <?php if(Input::Get("postType") != "video") echo "style='display:none'"; ?>>
				<div class="formField">
					<label for="video"><?php echo lang("VIDEO");?>
						<a href="#"  onclick="return false;" data-toggle="tooltip" data-placement="top" style="float:right" title="Supported formats for uploaded videos: 3g2, 3gp, 3gpp, asf, avi, dat, divx, dv, f4v, flv, m2ts, m4v, mkv, mod, mov, mp4, mpe, mpeg, mpeg4, mpg, mts, nsv, ogm, ogv, qt, tod, ts, vob, wmv.">
						<span class="glyphicon glyphicon-question-sign"></span></a>
					</label>
					<input type='text' name='video' class="form-control" id="video" value="<?php echo Input::Get("video");?>" placeholder='Video link (3gp, avi, mov, mp4, mpeg, mpeg4, vob, wmv...etc).' />
				</div>
				<div class="formField">
					<label for="description"><?php echo lang("DESCRIPTION"); ?></label>
					<textarea name='description' id="description" rows='3' cols='50' class="form-control" placeholder='Video Description'><?php echo Input::Get("description");?></textarea>
				</div>
			</div>

			<div class="formField">
				<label for="defTime"><?php echo lang('POST_INTERVAL_SEC'); ?><a href="#"  onclick="return false;" data-toggle="tooltip" data-placement="top" style="float:right" title="The random interval is activated by default. the interval will be (Interval - Interval+30 seconds) Example: if you choose 60 Sec real interval will be 60 sec - 90 Sec" ><span class="glyphicon glyphicon-question-sign"></span></a></label>
				<select name='defTime' id="defTime" class="form-control">
					<?php 
						$selected = Input::Get('defTime');					
						if(isset($user->Options()->postInterval)){
							$selected = Input::Get('defTime') ? Input::Get('defTime') : $user->Options()->postInterval;
						}
						for($i = 10;$i<=1500;$i += 30){
							if($i==$selected){
								echo "<option value='$i' selected>$i Sec</option>";
							}else{
								echo "<option value='$i'>$i Sec</option>";
							}
							if($i==10) $i=0;
						}
					?>
				</select>
			</div>
			<br/>
			<div class="formField">
				<button onclick="return false;" class='btn btn-primary' id="post" name='post'>
					<span class="glyphicon glyphicon-send"></span> <?php echo lang("SEND_NOW"); ?> 
				</button>
				<button onclick="return false;" class='btn btn-primary' id="savepost" name='savepost'>
					<span class="glyphicon glyphicon-floppy-disk"></span> <?php echo lang("SAVE_POST"); ?>
				</button>
				<button onclick="return false;" class='btn btn-primary' id="scheduledpost">
					<span class="glyphicon glyphicon-time"></span> <?php echo lang("SCHEDULED_POSTS"); ?> 
				</button>
			</div>
			<div class="row scheduledpost" style="display:none">
				<div class="col-lg-12">
					<strong><?php echo lang('POST_INTERVAL'); ?></strong>
					<a href="#" data-toggle="tooltip" data-placement="top" style="float:right" title="Minimum post interval is 5 Minutes" onclick="return false;"><span class="glyphicon glyphicon-question-sign"></span></a>
					<div class="row">
						<div class="col-lg-3">
							<div class="input-group">
								<span class="input-group-addon">
									<input type="radio" name="timeType" id="intervalMunite" value="minute" checked />
								</span>
								<span class="form-control"><?php echo lang('MINUTES'); ?></span>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="input-group">
								<span class="input-group-addon">
									<input type="radio" name="timeType" id="intervalHour" value="hour" />
								</span>
								<span class="form-control"><?php echo lang('HOURS'); ?></span>
							</div>
						</div>
						<div class="col-lg-6">
							
							<select name='scheduledPostInterval' id="scheduledPostInterval" class="form-control">
								<?php 
								for($i = 1;$i<=90;$i++){
									if($i == 5) echo "<option value='$i' selected>$i</option>";
									else echo "<option value='$i'>$i</option>";
								}
								?>
							</select>
							
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6">
							<label for="scheduledPostTime">
								<?php echo lang('SCHEDULE_POST_START'); ?>
								
							</label>
							<div class="form-group">
									<div class='input-group date'>
											<input type='text' class="form-control" id='scheduledPostTime' />
											<span class="input-group-addon">
													<span class="glyphicon glyphicon-calendar"></span>
											</span>
									</div>
									<small style="color:red;margin:5px">
										<?php 
											$currentServerTime = new DateTime();
											echo "Current server time : ".$currentServerTime->format('Y-m-d H:i'); 
										?>
									</small>
							</div>
													</div>
						<div class="col-lg-6">
							<label for="scheduledPostApp"><?php echo lang('FB_APP'); ?></label>
							<select name='scheduledPostApp' id="scheduledPostApp" class="form-control">
								<?php
									if($fb->AppsList()){
										$selected = Input::Get('scheduledPostApp') ? Input::Get('scheduledPostApp') : $fbaccount->UserFbAccountDefaultApp();
										foreach($fb->AppsList() as $app){
											$select = $selected == $app->appid ? "selected" : "";
											if($fb->GetAccessToken($app->appid)){
												echo "<option value='".$app->appid."' ".$select.">".$app->app_name."</option>";
											}
										}
									}
								?>
							</select>
						</div>
					</div>
					
					<div class="row">
						<div class="col-lg-12">
							<button onclick="return false;" class='btn btn-primary' id="saveScheduledPost" name='scheduledpost'>
								<span class="glyphicon glyphicon-time"></span> <?php echo lang("SAVE_SCHEDULED_POSTS"); ?> 
							</button>
						</div>
					</div>
				</div>
			</div>
			<div class="messageBox"></div>
		</div>
	  </div>
    </div>
	<div class="col-sm-6">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><?php echo lang("POST_PREVIEW"); ?></h3>
        </div>
        <div class="panel-body">
			<div class="postPreview">
			<div class="post">
				<div class="PreviewPoster">
					<img src='http://graph.facebook.com/<?php echo $fbaccountDetails->getFbid(); ?>/picture?redirect=1&height=40&width=40&type=normal' style='vertical-align:top;'  onerror="this.src = 'theme/default/images/facebookUser.jpg'" />
					<span class="userFullName">
						<?php
						if($fbaccountDetails->getLastname() || $fbaccountDetails->getFirstname()) 
							echo $fbaccountDetails->getLastname() . " " . $fbaccountDetails->getFirstname();
						else
							echo "Facebook User";
						?>
					</span>
					<span class="postPreviewDetails">
						<?php echo lang("NOW"); ?>· 
						<?php
							if($fbaccount->UserFbAccountDefaultApp()){
								echo Facebook::App($fbaccount->UserFbAccountDefaultApp())->app_name;
							}else{
								echo lang("APP_NAME"); 
							}
							
						?>
					</span>
					<div class="clear"></div>
				</div>
				<p class="message"><span class="defaultMessage"></span></p>
				
				<a href="#" class="previewPostLink">
					<div class="previewLink"></div>
					<div class="postDetails">
						<p class="name">
							<span class="defaultName"></span>
						</p>
						<p class="description">
							<span class="defaultDescription"></span>
							<span class="defaultDescription"></span>
							<span class="defaultDescription"></span>
							<span class="defaultDescription"></span>
							<span class="defaultDescription"></span>
						</p>
						<p class="caption"><span class="defaultCaption"></span></p>
					</div>
				</a>
			</div>
		</div>
		</div>
	  </div>
    </div>
</div>

<div class="panel panel-default">
  <div class="panel-body">
    <div class="postingDetails">
		<?php echo lang('NUMBER_OF_GROUPS'); ?> : <?php echo $fbaccount->GroupsCount(); ?> | <?php echo lang('ELAPSED'); ?> : <span class="totalPostTime">-</span> | <?php echo lang('TIME_LEFT'); ?> : <span class="leftTime">-</span>
	</div>
	<div class="controls">
	
		<button id="pauseButton" class="btn btn-primary" onclick="postPause()" disabled>
			<span class="glyphicon glyphicon-pause"></span><?php echo lang('PAUSE'); ?> 
		</button>
		
		<button id="resumeButton" class="btn btn-primary"  onclick="postResume()" disabled>
			<span class="glyphicon glyphicon-play"></span><?php echo lang('RESUME'); ?>  
		</button>
		
	</div>
  </div>
</div>
<div class="panel panel-default">
  <div class="panel-body">
		<table class="table table-bordered table-striped display dataTable" id="groupsDatabale">
			<thead><?php
				$checked = "";
				if($fbaccount->UserDefaultFbAccount()){
					echo "
					<tr>
						<td></td>
						<td>Timeline</td>
						<td colspan='3'>".lang('VIEW_PROFILE')."</td>
						<td>".lang('POST_STATUS')."</td>
					</tr>
					<tr class='groupName' id='me'>
						<td>
							<input type='checkbox' class='checkbox checkbox-style' name='selectGroup[0]' id='selectgroup_me' value='me' ".$checked.">
							<label for='selectgroup_me'></label>
						</td>
						<td class='groupTitle' id='group_me'>".$fbaccountDetails->getLastname()." ".$fbaccountDetails->getFirstname()."</td>
						<td colspan='3'><a href='https://www.facebook.com/".$fbaccount->UserDefaultFbAccount()."' target='_blank'>
							<span class='glyphicon glyphicon-link'></span>&nbsp; ".lang('VIEW_PROFILE')."</a>
						</td>
						<td>
						<span class='postStatus_me postStatus'></span>
						</td>
					</tr>";
				}
			?>
				<tr>
					<td colspan="6" class="groupsOptions">
						<div class="form-group">
							<span><?php echo lang('GROUP_BY_CATEGORY'); ?></span>
							<?php 
								$currentGroupCategory = Session::exists("groupscategory") ? Session::get("groupscategory") : -1; 
							?>
							<select name="groupscategory" class="form-control" onchange="this.form.submit()">	
								<option value="-1" <?php if($currentGroupCategory == -1) echo "selected" ?> >All</option>

								<?php
					  			foreach ($groupsCategories as $gc) {
					  				if($currentGroupCategory == $gc->id) 
					  					echo "<option value=".$gc->id." selected >".$gc->category_name."</option>";
					  				else
					  					echo "<option value=".$gc->id.">".$gc->category_name."</option>";
					  			}?>
					  		</select>
					  		<button class='btn btn-danger' name="deleteCategory" value="<?php echo $currentGroupCategory; ?>" ><span class="glyphicon glyphicon-trash"></span></button>
				  		</div>
				  		<div class="form-group">
				  			<span><?php echo lang('ADD_NEW_CATEGORY'); ?></span>
				  			<input type="text" name="newCategoryName" class="form-control" />
							<input type="submit" name="addCategory" value="<?php echo lang('ADD'); ?>" class="btn btn-default" />
						</div>	
				  		<div class="form-group">
							<span><?php echo lang('SEARCH'); ?></span>
							<input type="text" id="datatableSearchField" class="form-control" />
				  		</div>
					</td>
				</tr>
				<tr>
					<td width="20px">
						<input type='checkbox' id="checkbox-all" class="check-all checkbox-style" name='selectAllGroup' <?php if(Input::Get("selectAllGroup")) echo "checked"?>>
						<label for="checkbox-all"></label>
					</td>
					<td><?php echo lang('GROUP_NAME'); ?></td>
					<td><?php echo lang('PRIVACY'); ?></td>
					<td><?php echo lang('VISIT_GROUP'); ?></td>
					<td><?php echo lang('POST_STATUS'); ?></td>
					<td></td>
				</tr>
			</thead>
			<tbody>
			<?php
				$privacy = array('OPEN' => 'eye-open', 'CLOSED' => 'eye-close', 'SECRET' => 'folder-close');
				if($groups){
					foreach($groups as $group){
							
						if(isset($_POST['selectGroup'][$i])) $checked = "checked='checked'";
						echo "<tr class='groupName' id='".$group['id']."'>
								<td>

								<input type='checkbox' class='checkbox checkbox-style' name='selectGroup[".$i."]' id='selectgroup_".$group['id']."' value='".$group['id']."' ".$checked.">
								<label for='selectgroup_".$group['id']."'></label>
								</td>
								<td class='groupTitle' id='group_".$group['id']."'>
								<input type='hidden' name='selectGroupName[".$i."]' value='".$group['name']."' />
								".$group['name']."</td>
								<td><span class='glyphicon glyphicon-".$privacy[$group['privacy']]."'></span>&nbsp;".lang($group['privacy'])."<input type='hidden' name='selectGroupPrivacy[".$i."]' value='".$group['privacy']."'></td>
								<td><a href='https://www.facebook.com/groups/".$group['id']."' target='_blank'><span class='glyphicon glyphicon-link'></span>&nbsp; ".lang('VISIT_GROUP')."</a></td>
								<td>
								<span class='postStatus_".$group['id']." postStatus'></span>
								</td>
								<td>
								<button class='btn btn-danger' name='removeGroup' value='".$group['id']."' title='Remove from current category'><span class='glyphicon glyphicon-trash'></span></button>
								<button onclick='return false;' data-group='[{\"id\":\"".$group['id']."\",\"name\":\"".$group['name']."\",\"privacy\":\"".$group['privacy']."\"}]' class='btn btn-primary addToCategory' value='".$group['id']."'><span class='glyphicon glyphicon-plus'></span></button>
								</td>
							</tr>";
					}
				}
			?>
			</tbody>
		</table>

		<!-- Add group to category -->
		<script>
		$(function(){
			group = null;
			$('#groupsDatabale').on('click','.addToCategory', function() {
				group = $( this ).attr("data-group");
				// Clear message box
				$(".addCateMsgBoxModal").html("");
				// and finally show the modal
				$( '#addToCategoryModal' ).modal({ show: true });
				return false;
			});	

			$('#modalAddCateBtn').click(function() {
				// Clear message box
				$(".addCateMsgBoxModal").html("");
				category = $('.groupscategories', '#addToCategoryModal').val();
				
				$("#modalAddCateBtn").prop('disabled', false);
				$.post("ajax/groupcategory.php",
				{
					category: category,
					group: group
				},
				function(data){
					if(data == "true"){
						alertBox("<?php echo lang('GROUP_ADDED_SUCCESS'); ?>","success",".addCateMsgBoxModal",false);
					}else{
						alertBox(data,"danger",".addCateMsgBoxModal",false);
					}
				});

				$("#modalAddCateBtn").prop('disabled', true);
				
			});

		});	
		</script>
		<div id="addToCategoryModal" class="modal fade" role="dialog" data-backdrop="static">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title"><?php echo lang("ADD_GROUP_TO_CATEGORY"); ?></h4>
					</div>
					<div class="modal-body">
						<div class="addCateMsgBoxModal"></div>
						<select name="groupscategoriesAdd" class="form-control groupscategories">
							<?php
				  			foreach ($groupsCategories as $gc) {
				  				echo "<option value=".$gc->id.">".$gc->category_name."</option>";
				  			}?>
				  		</select>
					</div>
					<div class="modal-footer">
						<a type="button" class="btn btn-default" data-dismiss="modal"><?php echo lang("CLOSE"); ?></a>
						<a type="button" id="modalAddCateBtn" class="btn btn-primary"><?php echo lang("ADD"); ?></a>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>
</form>
<?php
$template->footer();
?>	
