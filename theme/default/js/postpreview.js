	/*
 * Spintax for post preview
 * 
 */
var SPINTAX_PATTERN = /\{[^"\r\n\}]*\}/;
var spin = function (spun) {
	var match;
	while (match = spun.match(SPINTAX_PATTERN)) {
		match = match[0];
		var candidates = match.substring(1, match.length - 1).split("|");
		spun = spun.replace(match, candidates[Math.floor(Math.random() * candidates.length)])
	}
	return spun;
}
	
	/*
 * Extract root domain name from string
 * @param url string : the url
 */
function extractDomain(url) {
    var domain;
    //find & remove protocol (http, ftp, etc.) and get domain
    if (url.indexOf("://") > -1) {
        domain = url.split('/')[2];
    }
    else {
        domain = url.split('/')[0];
    }

    //find & remove port number
    domain = domain.split(':')[0];
	
    return domain;
}
 /*
 *
 * Testing the url given is a youtube video url
 * @param url string
 *
 */
 function IsVideo(url) {    
      var regexp = /^(.*\.(?!(3g2|3gp|3gpp|asf|avi|dat|divx|dv|f4v|flv|m2ts|m4v|mkv|mod|mov|mp4|mpe|mpeg|mpeg4|mpg|mts|nsv|ogm|ogv|qt|tod|ts|vob|wmv)$))?[^.]*$/i;
	  return !regexp.test(url);	  
 }

  /*
 *
 * Testing the url given is a video url
 * @param url string
 *
 */
 function IsYoutubeVideo(url) {    
      var regexp = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/;
	  return regexp.test(url);	  
 }
/*
* GetSiteDetails 
* @param url string : the url of the site that we want to retrive its details
* @param xpath string
*
*/
function GetSiteDetails(URL, xpath, callback){
	var root = "http://query.yahooapis.com/v1/public/yql?format=json";
	var yahooApiUrl = root + "&q=select content from html where url = '"+URL+"' and xpath = '"+xpath+"'"
	$.ajax({
        type: "GET",
        url: yahooApiUrl,
        dataType: "jsonp",
        success: callback,
        error: function(request, status, error) {}
    });
	
}
 /*
*
* Default post preview reset the preview post to the default preview.
*
*/
 function defaultPreview(){
	 if(!$("input[name='picture']").val()){
		$(".postPreview .picture").html('src',"");
	 }
	 if(!$("input[name='name']").val()){
		$(".postPreview .name").html("<span class='defaultName'></span>");
	 }
	 if(!$("input[name='description']").val()){
		 $(".postPreview .description").html("<span class='defaultDescription'></span><span class='defaultDescription'></span><span class='defaultDescription'></span><span class='defaultDescription'></span><span class='defaultDescription'></span>");
	}
	if(!$("input[name='caption']").val()){
		$(".postPreview .caption").html("<span class='defaultCaption'></span>");
	}
 }
 
$( document ).ready(function() {
	// Preview instant update (message)
	$('#message').bind('input propertychange change', function() {
		if($.trim($(this).val()) != ""){
			var text = $(this).val();
			text = replaceEmoticons(text);
			$(".postPreview .message").html(spin(text.replace(/(?:\r\n|\r|\n)/g, '<br />')));
		}else{
			$(".postPreview .message").html("<span class='defaultMessage'></span>");
		}

	
	})

	$('#video').bind('input propertychange change', function() {
		if($.trim(".video") != ""){
			$(".previewLink").html("<video controls><source src='"+spin($(this).val())+"'></source></video>");
		}
	});

	// Preview instant update (link)
	$('#link').bind('input propertychange', function() {

		defaultPreview();

		var link = spin($(this).val());
		$(".alerts").hide();
		if($.trim(link) != ""){
				if(LinkIsValid(link)){
					$(".previewPost .previewPostlink").html(link);
					if(IsYoutubeVideo(link)){

						var videoID = link.match(/=([^\&\?\/]+)/)[1];
						 
						$(".previewLink").html("<iframe src='https://www.youtube.com/embed/"+videoID+"' width='470px' height='300px' frameborder='0' allowfullscreen='allowfullscreen'></iframe>");
						
						$.getJSON("ajax/page.php?youtube="+videoID, function(result){
							if(!$("input[name='name']").val()){
								$(".postPreview .name").html(result['title']);
							}
							if(!$("textarea[name='description']").val()){
								$(".postPreview .description").html(result['title']);
							}
						});
						
						if(!$("input[name='caption']").val()){
							$(".postPreview .caption").html("youtube.com");
						}
						
					}else if(IsVideo(link)){
						$(".previewLink").html("<video controls><source src='"+link+"'></source></video>");
					}else{

						$(".postPreview .caption").html(extractDomain(link));
						
						GetSiteDetails(link,"//head/title", function(response) {
							if($( "#postForm #name" ).val() == ""){
								try{
									$(".postPreview .name").html(response.query.results.title);
								}catch(arr){}
							}
						});
						
						
						GetSiteDetails(link,"//head/meta[@property=\"og:image\"]", function(response) {
							if($( "#postForm #picture" ).val() == ""){
								try{
									$(".previewLink").html("<img src='"+response.query.results.meta.content+"'>");
								}catch(arr){}
							}
						});
						
						GetSiteDetails(link,"//head/meta[@name=\"description\"]", function(response) {
							if($( "#postForm #description" ).val() == ""){
								try{
								$(".postPreview .description").html(response.query.results.meta.content);
								}catch(arr){}
							}
						});
						
						GetSiteDetails(link,"//head/meta[@property=\"og:description\"]", function(response) {
							if($( "#postForm #description" ).val() == ""){
								try{
									$(".postPreview .description").attr('src',response.query.results.meta.content);
								}catch(arr){}
							}
						});
					 }
				}else{
					alertBox("Invalid link","danger",false,true);
					defaultPreview();
				}
		}else{
			defaultPreview();
		}
	});
	
	// Preview instant update (picture)
	$('#picture').bind('input propertychange', function() {
		var picture = spin($(this).val());
		if($.trim(picture) != ""){
			 $(".postPreview .previewLink").html("<img onerror='../images/defaultPreviewImg.png' src='"+picture+"' />");
		}else{
			$(".postPreview .previewLink").html("");
		}
	});
	
	
	// Preview instant update (name)
	$('#name').bind('input propertychange', function() {
		var name = spin($(this).val());
		if($.trim(name) != ""){
			 $(".postPreview .name").html(name);
		}else{
			$(".postPreview .name").html("<span class='defaultName'></span>");
		}
	});
	
	// Preview instant update (caption)
	$('#caption').bind('input propertychange', function() {
		var caption = spin($(this).val());
		if($.trim(caption) != ""){
			$(".postPreview .caption").html(caption);
		}else{
			$(".postPreview .caption").html("<span class='defaultCaption'></span>");
		}
	});
	
	// Preview instant update (description)
	$('#description').bind('input propertychange', function() {
		var description = spin($(this).val());
		if($.trim(description) != ""){
			$(".postPreview .description").html(description);
		}else{
			$(".postPreview .description").html("<span class='defaultDescription'></span><span class='defaultDescription'></span><span class='defaultDescription'></span><span class='defaultDescription'></span><span class='defaultDescription'></span>");
		}
	});


	// Preview instant update (picture)
	$('#image').bind('input propertychange', function() {
		var image = spin($(this).val());
		if($.trim(image) != ""){
			 $(".postPreview .previewLink").html("<img src='"+image+"' />");
			 $(".postPreview .previewLink img").error(function() {$(".postPreview .previewLink").html("");});
		}else{
			$(".postPreview .previewLink").html("");
		}
	});


	$( "#message" ).trigger('propertychange');

	if( $( "#postType" ).val() == "link" ){
		$( "#link" ).trigger('propertychange');
		$( "#picture" ).trigger('propertychange');
		$( "#name" ).trigger('propertychange');
		$( "#caption" ).trigger('propertychange');
		$( "#description" ).trigger('propertychange');
	}

	if( $( "#postType" ).val() == "image" ){
		$( "#image" ).trigger('propertychange');
	}

	if( $( "#postType" ).val() == "video" ){
		$( "#video" ).trigger('propertychange');
		$( "#description" ).trigger('propertychange');
	}
	

});