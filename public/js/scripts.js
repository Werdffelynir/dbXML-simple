$(document).ready(function(){

    /*$('.showerBlock .btn').click(function(){
        var parentBlock = $(this).parent().find('.toggle');
        $(parentBlock).fadeToggle();
    });*/
    
    $('.doc-btn').click(function(){
		var parentBlock = $(this).parent('.showerBlock').find('.toggle');
		console.log(parentBlock);
		$(parentBlock).fadeToggle();
	});

    function url(type){
        var url = null;
        if(type == 'base'){
            url = $('meta[data-url]').attr('data-url');
        }else if(type == 'theme'){
            url = $('meta[data-url]').attr('data-url-theme');
        }
        return url;
    }

}); // END (document).ready()