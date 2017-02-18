$(document).ready(function(){
  if(window.location.hash != "") { 
    $('.nav-tabs a[href="' + window.location.hash + '"]').click();
    $(window).scrollTop(0)
    return false;
  }
});
/*
* 	
* This function makes all groups checkbox checked/unchecked
*
*/
$(document).ready(function () {
	$("#checkbox-all").click(function () {
		$('#groupsDatabale tbody input[type="checkbox"],#datatable tbody input[type="checkbox"]').prop('checked', this.checked);
	});
});
		
/*
* Display alert
*
*/
function alertBox(message,type,errorHolder,showIcon,close = true){
	var icons = {};	
	icons['success'] = 'ok';
	icons['info'] = 'info';
	icons['warning'] = 'warning';
	icons['danger'] = 'exclamation';
				
	var html = "<div class='alert alert-"+type+"' role='alert'>";
	if(close) html += "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
	if(showIcon) html += "<span class='glyphicon glyphicon-"+icons[type]+"-sign' aria-hidden='true'></span>&nbsp;";
			html += message+"</div>";

	$( document ).ready(function() {
		if(errorHolder){
			$(errorHolder).hide();
			$(errorHolder).html(html);
			$(errorHolder).fadeIn(300);
		}else{
			$(".alerts").hide();
			$(".alerts").html(html);
			$(".alerts").fadeIn(300);
		}
	});
}

/* 
* Close erro panel
*/
$(".errorsPanelClose").click(function(){
	this.hide();
});

function replaceEmoticons(text) {
  var emoticons = {
    ":)" 	: 'smile',
	" :D" 	: 'grin',
    " :("	: 'frown',
    " :'("	: 'cry',
   	" :p" 	: 'tongue',
    " :3" 	: 'colonthree',
    " O:)" 	: 'angel',
    " 3:)" 	: 'deil',
   	" <3" 	: 'heart',
   	" :*"	: 'kiss',
	" o.O"	: 'confused',
	" ;)"	: 'wink',
	":O"	: 'gasp',
	"-_-"	: 'squint',
	">:O"	: '',
	"^_^"	: 'kiki',
	" 8-)"	: 'glasses',
	" 8|"	: 'sunglasses',
	"(^^^)"	: '',
	":|]"	: '',
	">:("	: '',
	" :v"	: 'pacman',
	":/"	: '',
	"(y)"	: 'like',
	/*":poop:": '',
	":putnam:": '',
	"<(\")"	: '',*/

  },
  patterns = [],
  metachars = /[[\]{}()*+?.\\|^$\-,&#\s]/g;

  // build a regex pattern for each defined property
  for (var i in emoticons) {
    if (emoticons.hasOwnProperty(i)){ // escape metacharacters
      patterns.push('('+i.replace(metachars, "\\$&")+')');
    }
  }

  // build the regular expression and replace
  return text.replace(new RegExp(patterns.join('|'),'g'), function (match) {
    return typeof emoticons[match] != 'undefined' ?
           ' <span class="emoji '+emoticons[match]+'"></span>' :
           match;
  });
}
 
$( document ).ready(function() {
	$('#emoticons a').click(function (event) {
		event.preventDefault();
	   var smiley = " "+$(this).attr('title');
	   ins2pos(smiley, 'message');
	   $( "#message" ).change();
	});
});

function ins2pos(str, id) {
   var TextArea = document.getElementById(id);
   var val = TextArea.value;
   var before = val.substring(0, TextArea.selectionStart);
   var after = val.substring(TextArea.selectionEnd, val.length);
   
   TextArea.value = before + str + after;
   setCursor(TextArea, before.length + str.length);
   
}

function setCursor(elem, pos) {
   if (elem.setSelectionRange) {
      elem.focus();
      elem.setSelectionRange(pos, pos);
   } else if (elem.createTextRange) {
      var range = elem.createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
   }
}