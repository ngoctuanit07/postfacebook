// Global variables 
var POST_IN_PROGRESS = false; // prevent posting same post multi times 
/*
* Check if the link given by the user is valid.
*
* @param url string
*
*/
 function LinkIsValid(url) {    
      var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
      return regexp.test(url);    
 }
 /*
 *
 * Language function
 * @param string string
 *
 */
function lang(string){
	if(langs[string] === undefined){
		return string;
	}
	return langs[string];
}
$( document ).ready(function() {
	$('#accessTokenURL').bind('input propertychange', function() {
		var at = $(this).val().match(/access_token=(.*)(?=&expires_in)/);
		if(at){$("#accessToken").val(at[1]);}
	});
});
/*
* 	
* This function checked the group checkbox if is unchecked otherwise unchecked it.
*
*/ 
$(function(){
   $('.groupTitle').click(function(event) {
		if($("#select"+this.id).is(":checked")){
			$("#select"+this.id).prop('checked', false);
		}else{
			$("#select"+this.id).prop('checked', true);
		}
    });
});
/*
* startTimer
* 
* Display a timer of posting on the (lefttime) span
*
*/
var groups = []; // List of selected groups
var TOTALPOSTINGTIME = 0; // in milliseconds
var leftTime = 0;
var postingInterval = 0;
var countGroup = 0;
var nextGroup = 0;
var timeDeff = 30000; // default 30 seconds

function random(min,max){
	min = parseInt(min);
	max = parseInt(max);
	return Math.floor(Math.random() * (max - min + 1)) + min;  
}

function startTimer(){
	var h = Math.floor(TOTALPOSTINGTIME / 36e5),
      m = Math.floor((TOTALPOSTINGTIME % 36e5) / 6e4),
      s = Math.floor((TOTALPOSTINGTIME % 6e4) / 1000);
	
    h= (h<10)?"0"+h: h;
    m= (m<10)?"0"+m: m;
    s= (s<10)? "0"+s : s;
	
    $(".leftTime").html("&sim; "+h+":"+m+":"+s);
    TOTALPOSTINGTIME = TOTALPOSTINGTIME - 1000;
		
   if( h==0 && m==0 && s==0 ){
		clearTimeout(leftTime);
		$(".leftTime").html(lang("DONE"));
		$("#postForm #post").prop('disabled', false);
		$("#postForm #post").removeClass("btnDisabled");
		alertBox(lang('POSTING_COMPLETED'),"success","#postForm .messageBox",true);	
		POST_IN_PROGRESS = false;
	}else{
		leftTime = setTimeout(startTimer,1000);
		$("#pauseButton").prop('disabled', false);
		$("#pauseButton").removeClass("btnDisabled");
	}
}

function postPause(){
  clearTimeout(leftTime);
  clearTimeout(postingInterval);
	$("#pauseButton").prop('disabled', true);
	$("#resumeButton").prop('disabled', false);
	$("#pauseButton").addClass("btnDisabled");
	$("#resumeButton").removeClass("btnDisabled");
}

function postResume(){
	clearTimeout(leftTime);
  clearTimeout(postingInterval);
  leftTime = setTimeout(startTimer,1000);
  postingInterval = setTimeout(posting,timeDeff);
	
	$("#pauseButton").prop('disabled', false);
	$("#resumeButton").prop('disabled', true);
	$("#pauseButton").removeClass("btnDisabled");
	$("#resumeButton").addClass("btnDisabled");
}
/*
*
* posting : handle the posting loop
* 
* @prama countGroup int : number of selected groups
* @param timeDiff : post time interval
* @param nextGroup int : next group id
*/
function posting() {
	nextGroup++;
	
	timeDeff = random($("#postForm #defTime").val()*1000,($("#postForm #defTime").val()*1000)+30000);

	if (nextGroup < countGroup) {
		send();
		postingInterval = setTimeout(posting,timeDeff);
	}else{
		clearTimeout(postingInterval);
		// Reinitial all variables 
		TOTALPOSTINGTIME = 0;
		groups.length = 0;
		leftTime = 0;
		countGroup = 0;
		nextGroup = 0;
		POST_IN_PROGRESS = false;
	}
}

/*
|--------------------------------
| Save post
|--------------------------
|
*/
function savePost(){
	if($.trim($("#postForm #message").val()) == "" && $.trim($("#postForm #link").val()) == "" && $.trim($("#postForm #image").val()) == "" && $.trim($("#postForm #video").val()) == ""){
		alertBox(lang('POST_EMPTY'),"danger","#postForm .messageBox",true);
	}else{
		if($("#postForm #postId").val() != "") {

			alertBox("<img src='theme/default/images/loading.gif' alt='loading'/>","","#postTitleModal .messageBoxModal",false,false);
			$.post(
				"ajax/savepost.php",
				{
					postId:$("#postForm #postId").val(),
					postType:$("#postForm #postType").val(),
					message:$("#postForm #message").val(),
					link:$("#postForm #link").val(),
					image:$("#postForm #image").val(),
					video:$("#postForm #video").val(),
					picture:$("#postForm #picture").val(),
					name:$("#postForm #name").val(),
					caption:$("#postForm #caption").val(),
					description:$("#postForm #description").val(),
					action: 'update'
				},
				function(data){
					if($.trim(data) == "true"){
						alertBox(lang('POST_UPDATED_SUCCESS'),"success","#postForm .messageBox",true);
					}else if($.trim(data) == ""){
						alertBox(lang('EMPTY_RESPONSE'),"danger","#postForm .messageBox",true);
					}else{
						alertBox(data,"danger","#postForm .messageBox",true);
					}
				});

		}else{
			$('#postTitleModal').modal('show');
			$("#savePostModal").click(function(){
				if($.trim($("#postTitleModal #postTitle").val()) != ""){
					alertBox("<img src='theme/default/images/loading.gif' alt='loading'/>","","#postTitleModal .messageBoxModal",false,false);
					$.post(
						"ajax/savepost.php",
						{
							post_title:$("#postTitleModal #postTitle").val(),
							postType:$("#postForm #postType").val(),
							message:$("#postForm #message").val(),
							link:$("#postForm #link").val(),
							image:$("#postForm #image").val(),
							video:$("#postForm #video").val(),
							picture:$("#postForm #picture").val(),
							name:$("#postForm #name").val(),
							caption:$("#postForm #caption").val(),
							description:$("#postForm #description").val(),
							action: 'add'
						},
						function(data){
							if($.trim(data) != ""){
								if(isNaN(data)){
									alertBox(data,"danger","#postTitleModal .messageBoxModal",true);
								}else if($("#postForm #postId").val() != ""){
									alertBox(lang('POST_ALREADY_SAVED'),"danger","#postTitleModal .messageBoxModal",true,false);
								}else{
									alertBox(lang('POST_SAVED_SUCCESS'),"success","#postTitleModal .messageBoxModal",true);
									$("#postForm #postId").val(data);
								}
							}else{
								alertBox(lang('EMPTY_RESPONSE'),"danger","#postTitleModal .messageBoxModal",true);
							}
						});
				}else{
					alertBox(lang('CHOOSE_TITLE_POST'),"danger","#postTitleModal .messageBoxModal",true);
				}
			});
		}
	}
}
/*------------------------------------------*/

$( document ).ready(function() {
	// postTypeMessage click event when click (define post type and make current post type active) 
	$( ".postTypeMessage" ).click(function() {
		$("#postLinkDetails,#postImageDetails,#postVideoDetails").hide();
		$(".postTypeLink,.postTypeImage,.postTypeVideo").removeClass("postTypeActive");
		$("input[name='postType'").val("message");
		$(this).addClass("postTypeActive");
	});
	
	// postTypeLink click event when click (define post type and make current post type active) 
	$( ".postTypeLink" ).click(function() {
		$("#postLinkDetails").show();
		$("#postImageDetails").hide();
		$("#postVideoDetails").hide();
		$(this).addClass("postTypeActive");
		$(".postTypeMessage").removeClass("postTypeActive");
		$(".postTypeImage").removeClass("postTypeActive");
		$(".postTypeVideo").removeClass("postTypeActive");
		$("input[name='postType'").val("link");
	});
	
	// postTypeImage click event when click (define post type and make current post type active) 
	$( ".postTypeImage" ).click(function() {
		$("#postImageDetails").show();
		$("#postVideoDetails").hide();
		$("#postLinkDetails").hide();
		$(this).addClass("postTypeActive");
		$(".postTypeMessage").removeClass("postTypeActive");
		$(".postTypeLink").removeClass("postTypeActive");
		$(".postTypeVideo").removeClass("postTypeActive");
		$("input[name='postType'").val("image");
	});

	// postTypeVideo click event when click (define post type and make current post type active) 
	$( ".postTypeVideo" ).click(function() {
		$("#postVideoDetails").show();
		$("#postImageDetails").hide();
		$("#postLinkDetails").hide();
		$(this).addClass("postTypeActive");
		$(".postTypeMessage").removeClass("postTypeActive");
		$(".postTypeImage").removeClass("postTypeActive");
		$(".postTypeLink").removeClass("postTypeActive");
		$("input[name='postType'").val("video");
	});

	// #postForm #post click event => Post validation 
	$("#postForm #post").click(function(){
		$("#postForm .messageBox").removeClass("error");
		$("#postForm .messageBox").html("");
		
		if($("#postForm #postType").val() == "message" && $.trim($("#postForm #message").val()) == ""){
			alertBox(lang('POST_EMPTY'),"danger","#postForm .messageBox",true);	
		}else if($("#postForm #postType").val() == "link" && $.trim($("#postForm #link").val()) == ""){
			alertBox(lang('POST_EMPTY'),"danger","#postForm .messageBox",true);	
		}else if($("#postForm #postType").val() == "image" && $.trim($("#postForm #image").val()) == ""){
			alertBox(lang('POST_EMPTY'),"danger","#postForm .messageBox",true);	
		}else{
			post();
		}
	});
	
});

/*
*
* Send : get the current group id from the group[] array, call getJSON() function
* @param nextGroup int : counter
*
*/
function send()
{
	var unexpectedPostingError = true;
	var currentGroup = groups[nextGroup];
	POST_IN_PROGRESS = true;

	// update the left time
	var duree = random(parseInt($("#postForm #defTime").val()),parseInt($("#postForm #defTime").val())+30) * (countGroup-nextGroup);
	TOTALPOSTINGTIME = duree*1000;

	// Clear errors
	$('.postingStatusErrors').html("");
	
	$.post( "ajax/accesstoken.php", {isAccessTokenValid:'true'},function( data ) {
		if(data == "false"){
			postPause();
			alertBox(lang('CONNECTION_ERROR_OR_INVALID_ACCESS_TOKEN'),"danger",false,false);
		}
	});
	
	if(!$('#selectgroup_' + currentGroup).is(":checked")) return false;
	
	// Get post data
	var params = {};
	
	params["groupID"] = currentGroup;
	params["postType"] = $("#postForm #postType").val();
	
	if($.trim($("#postForm #message").val()) != ""){
		params["message"] = $("#postForm #message").val();
	}
		
		if($("#postForm #postType").val() == "image"){
			if($.trim($("#postForm #image").val()) != ""){
				params["image"] = $("#postForm #image").val();
			}
		}

		if($("#postForm #postType").val() == "video"){
			if($.trim($("#postForm #video").val()) != ""){
				params["file_url"] = $("#postForm #video").val();
			}
			if($.trim($("#postForm #description").val()) != ""){
				params["description"] = $("#postForm #description").val();
			}
		}
		
		if($("#postForm #postType").val() == "link"){
			if($.trim($("#postForm #link").val()) != "") 
				params["link"] = $("#postForm #link").val();
			
			if($.trim($("#postForm #picture").val()) != "") 
				params["picture"] = $("#postForm #picture").val(); 
			
			if($.trim($("#postForm #name").val()) != "") 
				params["name"] = $("#postForm #name").val();
			
			if($.trim($("#postForm #caption").val()) != "") 
				params["caption"] = $("#postForm #caption").val();
			
			if($.trim($("#postForm #description").val()) != "") 
				params["description"] = $("#postForm #description").val();
		}

	$.post( "ajax/post.php", params,function( data ) {

		if(data == ""){
			$('#'+currentGroup).removeClass('postingSuccess');
			$('#'+currentGroup).addClass('postingError');
			$(".postStatus_"+currentGroup).html("<span class='glyphicon glyphicon-info-sign'></span> "+lang('EMPTY_REQUEST'));
			return;
		}
		try{
			var result = JSON.parse(data);
			if(result.status == "success"){
				$('#'+currentGroup).removeClass('postingError');
				$('#'+currentGroup).addClass('postingSuccess');
				
				$(".postStatus_"+currentGroup).html("<a href='https://www.facebook.com/"+result.id+"' target='_blank'><span class='glyphicon glyphicon-ok'></span> "+lang('VIEW_POST')+" </a>");
				
			}else{
				$('#'+currentGroup).removeClass('postingSuccess');
				$('#'+currentGroup).addClass('postingError');
				$(".postStatus_"+currentGroup).html("<span class='glyphicon glyphicon-remove'></span> "+result.message);
			}
		}catch(ex){
			$('#'+currentGroup).removeClass('postingSuccess');
			$('#'+currentGroup).addClass('postingError');
			$(".postStatus_"+currentGroup).html("<span class='glyphicon glyphicon-info-sign'></span> "+lang('UNEXPECTED_ERROR_CHECK_INTERNET_CONNECTION'));
		}
	}).fail(function(data) {
		$('#'+currentGroup).removeClass('postingSuccess');
		$('#'+currentGroup).addClass('postingError');
		$(".postStatus_"+currentGroup).html("<span class='glyphicon glyphicon-info-sign'></span> "+lang('UNEXPECTED_ERROR_CHECK_INTERNET_CONNECTION'));
	});
}
/*
* Post : This function run the posting process
*
*/
function post(){
	
	timeDeff = random($("#postForm #defTime").val()*1000,($("#postForm #defTime").val()*1000)+30000);

	// Clear groups, groupCount vars
	groups = [];
	countGroup = 0;
	
	// Get all checked groups
	$('.checkbox:checked').each(function(){
		groups.push($(this).val());
		countGroup++;
	});

	if (POST_IN_PROGRESS == false) {
		if(countGroup != 0){
			$("#postForm #post").prop('disabled', true);
			$("#postForm #post").addClass("btnDisabled");
			
			alertBox(lang('POSTING_WAIT'),"info","#postForm .messageBox",true);	
						
			// Set the left time
			var duree = random(parseInt($("#postForm #defTime").val()),parseInt($("#postForm #defTime").val())+30) * (countGroup-1);
			TOTALPOSTINGTIME = duree*1000;
	
			$(".totalPostTime").html("&sim; "+Math.round(((parseInt($("#postForm #defTime").val())+15 )* (countGroup-1))/60).toFixed(2)+" "+lang('MINUTES'));	
			
			startTimer();
			send();
			postingInterval = setTimeout(posting,timeDeff);

		}else{
			alertBox(lang('CHOOSE_GROUP_TO_POSTIN'),"danger","#postForm .messageBox",true);	
		}
	}else{
		alertBox(lang('POST_IN_PROGRESS_REFRESH_PAGE_TO_CANCEL'),"danger");
	}
}

$(function(){
	$("#postForm #savepost").click(function(){ savePost(); });	

	$("#postForm #scheduledpost").click(function(){
		$("#postForm .scheduledpost").toggle('fast');
	});
	
	$("#postForm #saveScheduledPost").click(function(){
			// clear groups var
			groups = [];
			countGroup = 0;
			// Get all checked groups
			$('.checkbox:checked').each(function(){
				groups.push($(this).val());
				countGroup++;
			});

			if($.trim($("#postForm #message").val()) == "" && $.trim($("#postForm #link").val()) == "" && $.trim($("#postForm #image").val()) == "" && $.trim($("#postForm #video").val()) == ""){
				alertBox(lang('POST_EMPTY'),"danger","#postForm .messageBox",true);
			}else if($("#postForm #postId").val() == ""){
				alertBox(lang('PLEASE_SAVE_POST_FIRST'),"danger","#postForm .messageBox",true);	
			}else if(countGroup == 0){
				alertBox(lang('CHOOSE_GROUP_TO_POSTIN'),"danger","#postForm .messageBox",true);	
			}else{
				var pi = $("#scheduledPostInterval","#postForm").val();
				var post_interval = $("input[type='radio']:checked","#postForm").val() == "minute" ? pi : pi*60 ;
				
				alertBox("<img src='theme/default/images/loading.gif' alt='loading'/>","","#postForm .messageBox",false,false);
				
				// Disable save schedule post
				$("#postForm #saveScheduledPost").prop('disabled', true);
				
				$.post(
				"ajax/savescheduledposts.php",
				{
						scheduledPostTime: $("#scheduledPostTime","#postForm").val(),
						post_interval: post_interval,
						targets: JSON.stringify(groups),
						post_id: $("#postForm #postId").val(),
						post_app: $("#scheduledPostApp","#postForm").val(),
						fb_account: $("#scheduledFbAccount","#postForm").val()
				},
				function(data){
					if(data == "true"){
						alertBox(lang('SCHEDULE_SAVED_SUCCESS'),"success","#postForm .messageBox",false);
					}else{
						alertBox(data,"danger","#postForm .messageBox",false);
					}
					// Re-enable save schedule post
					$("#postForm #saveScheduledPost").prop('disabled', false);
				});
			}
	});
	
});