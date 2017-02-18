var LoginModalController = {
    tabsElementName: ".logmod__tabs li",
    tabElementName: ".logmod__tab",
    inputElementsName: ".logmod__form .input",
    hidePasswordName: ".hide-password",
    
    inputElements: null,
    tabsElement: null,
    tabElement: null,
    hidePassword: null,
    
    activeTab: null,
    tabSelection: 0, // 0 - first, 1 - second
    
    findElements: function () {
        var base = this;
        
        base.tabsElement = $(base.tabsElementName);
        base.tabElement = $(base.tabElementName);
        base.inputElements = $(base.inputElementsName);
        base.hidePassword = $(base.hidePasswordName);
        
        return base;
    },
    
   
    addClickEvents: function () {
    	var base = this;
        
        base.hidePassword.on("click", function (e) {
            var $this = $(this),
                $pwInput = $this.prev("input");
            
            if ($pwInput.attr("type") == "password") {
                $pwInput.attr("type", "text");
                $this.text("Hide");
            } else {
                $pwInput.attr("type", "password");
                $this.text("Show");
            }
        });
        base.inputElements.find("label").on("click", function (e) {
           var $this = $(this),
               $input = $this.next("input"); 
            $input.focus();
        });     
        return base;
    },
   
    initialize: function () {
        var base = this;        
        base.findElements().addClickEvents();
    }
};

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
                
    var html = "<div class='alert alert-"+type+" alert-singnin' role='alert'>";
    if(close) html += "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>";
    if(showIcon) html += "<span class='glyphicon glyphicon-"+icons[type]+"-sign' aria-hidden='true'></span>&nbsp;";
            html += message+"</div>";

    $( document ).ready(function() {
        if(errorHolder){
            $(errorHolder).html(html);
        }else{
            $(".alerts").html(html);
        }
    });
}

$(document).ready(function() {
    LoginModalController.initialize();
    
    $( "#signupBtn" ).click(function(){
        alertBox("<img src='theme/default/images/loading.gif' alt='loading'/>","",".signupMessageBox",false,false);
        $.post(
            "ajax/signin.php",
            {
                signup: "true",
                username: $("#signup_username",".signup").val(),
                password: $("#signup_password",".signup").val(),
                repassword: $("#signup_repassword",".signup").val(),
                email: $("#signup_email",".signup").val(),
            },
            function(data){
                if(data == "true"){
                    window.setTimeout('location.reload()', 0);
                }else if(data == ""){
                    alertBox("Empty request","danger",".signupMessageBox",false,false);
                }else{
                    var errors = "<ul>";
                    $.each(data, function (index, item) { 
                        errors += "<li>"+item+"</li>";
                    });
                    errors += "</ul>";

                    alertBox(errors,"danger",".signupMessageBox",false,false);
                }
                
            }
        );
    });

});