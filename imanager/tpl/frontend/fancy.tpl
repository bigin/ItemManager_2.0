<div id="sgallery-screen">
    <script type="text/javascript">
    (function($) {
        var cache = [];
        // Arguments are static image paths
        $.preLoadImages = function() {
            var args_len = arguments.length;
            for (var i = args_len; i--;) {
                var cacheImage = document.createElement("img");
                cacheImage.src = arguments[i];
                cache.push(cacheImage);
            }
        }
    })($);
    $.preLoadImages([[preload]]);
    </script>
    [[loop]]
    [[paginator]]
</div>
<div class="winframes hidden" id="draggable">
    <a class="closer" href="">
        <img id="imgclose" alt="Close window" src="http://stuff-depot.ehret-studio.de/plugins/egallery/images/close.png">
    </a>
    <img id="screen-img" class="bimage" alt="" src="">
</div>
<a href="" class="hidden closer" style="display: none;"><p class="reflector">&nbsp;</p></a>
<script type="text/javascript">
    $(document).ready(function ($) {
	    $("#screen-img").fadeOut("slow");
        $("a.linkpic").click(function () {        
            var iid = $($(this)).attr("id");
			var newimg = new Image();
    		//newimg.src = mytemp;
            var iTitle = new Array([[titlearray]]);
            var iContent = new Array([[contentarray]]);
            var iUrl = new Array([[preload]])

            newimg.src = iUrl[iid];
            var width = newimg.width;

            $('#draggable').append('<div class="idata"><h4 class="ititle">'
                + iTitle[iid] +'</h4>'+ iContent[iid] +'</div>');
				
			$("a.hidden").css("display", "inline");
			$("#draggable").css("display", "block");
			$("#draggable").css("position", "absolute");
				
			$("#draggable").css("width", width);
			$("#draggable").css("height", "auto");
			$("#draggable").css("left", "50%");
			$("#draggable").css("margin-left", -(width / 2));					
				
            $("#screen-img").fadeOut("slow", function() {
                $("#screen-img").attr("src",  iUrl[iid]);
				$("#screen-img").fadeIn();
            });
            return false;// "cancel" the default behavior of following the link
        });
		// close button for window
		$("a.closer").click(function () {
			$("#draggable").css("display", "none");
			$("a.hidden").css("display", "none");
			$("#screen-img").fadeOut("slow");
			$(".idata").empty().remove();
			return false;
		});
        // draggable div
        $(function(){$( "#draggable" ).draggable();});
    });
</script>
