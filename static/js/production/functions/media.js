function get_aspectRatio(w,h){
    var w=parseInt(w);
    var h=parseInt(h);
    var aspectRatio = [];
        aspectRatio[0] = 1;
        aspectRatio[1] = 1;

    if(h == w){
        aspectRatio[0] = 1;
        aspectRatio[1] = 1;
    }else{
        var mode = null;

        if(h>w){
            dividend  = h;
            divisor   = w;
            mode      ='portrait';
        }

        if(w>h){
            dividend   = w;
            divisor    = h;
            mode       = 'landscape';
        }

        var gcd = -1;
        while(gcd == -1){
            remainder = dividend%divisor;
            if(remainder == 0){
                gcd = divisor;
            }else{
                dividend  = divisor;
                divisor   = remainder;
            }
        }
        var hr         = w/gcd;
        var vr         = h/gcd;
        aspectRatio[0] = hr;
        aspectRatio[1] = vr;
    }
    console.log(aspectRatio);
    return aspectRatio;
}
function video_bg_fit($container, $video, $ratio){
	var width = $container.outerWidth(),
		pWidth,
		height = $container.outerHeight(),
		pHeight;
	if($video.hasClass("swiper-video-url")){
		var $player = $($video.find('iframe')[0]),
			pRatio = $ratio;//16 / 9;					    	
	}else{
		var $player = $($video.find('video')[0]),
			pRatio = $ratio;//16 / 9;	
	}
	if (width / pRatio < height) {
		pWidth = Math.ceil(height * pRatio);
		$player.width(pWidth).height(height).css({
			position: 'absolute',
			left: (width - pWidth) / 2,
			top: 0
		});
	} else {
		pHeight = Math.ceil(width / pRatio);
		$player.width(width).height(pHeight).css({
			position: 'absolute',
			left: 0,
			top: (height - pHeight) / 2
		});
	}
	console.log($container, $video, $player);
}

( function() {
	var youtube = document.querySelectorAll( ".youtube" );
    for (var i = 0; i < youtube.length; i++) {
        var source = "https://img.youtube.com/vi/"+ youtube[i].dataset.embed +"/maxresdefault.jpg";
        youtube[ i ].innerHTML = "<div class='play-button'/>" ;
        var image = new Image();
            image.src = source;
            image.setAttribute("class", "lazy");

            image.addEventListener( "load", function() {
                youtube[ i ].appendChild( image );
            }( i ) );
            youtube[i].addEventListener( "click", function() {
                this.classList.add("played");
                var mute = $("html").hasClass("Chrome");
                var iframe = document.createElement( "iframe" );
                    iframe.setAttribute( "class", "embed-responsive-item" );
                    iframe.setAttribute( "frameborder", "0" );
                    iframe.setAttribute( "allowfullscreen", "" );
                    iframe.setAttribute( "src", "https://www.youtube.com/embed/"+ this.dataset.embed +"?rel=0&showinfo=0&autoplay=1&mute="+mute );
                    this.innerHTML = "";
                    this.appendChild( iframe );
            } );    
    };
} )();

function fill_element(container, element, w, h){
	//container info
	var ratio_1 = get_aspectRatio(container.width(), container.height());
	    ratio_1 = ratio_1[0] / ratio_1[1];
	var container_layout = ratio_1 < 1 ? "portrait" : ratio_1 == 1 ? "square":"landscape";
	//element info
	var ratio_2 = get_aspectRatio(w, h);
	    ratio_2 = ratio_2[0] / ratio_2[1];
	var element_layout = ratio_2 < 1 ? "portrait" : ratio_2 == 1 ? "square":"landscape";
    
    console.log(container_layout+"-"+element_layout);

	switch(container_layout+"-"+element_layout){
		case "portrait-portrait":
		    if(ratio_1 < ratio_2){
		    	console.log(ratio_1+"<"+ratio_2)
                var width = (w*container.height())/h;
			    var height =  container.height();
			   	var left = -Math.ceil(Math.abs((container.width()-width)/2));
			    var top = 0;
		    }else{
		    	console.log(ratio_1+">"+ratio_2)
		    	var width = container.width();
			    var height = (h*container.width())/w;
			   	var left = 0;
			    var top = -Math.ceil(Math.abs((container.height()-height)/2));
		    }
		break;
		case "landscape-landscape":
		    if(ratio_1 < ratio_2){
		    	console.log(ratio_1+"<"+ratio_2)
                var width = (w*container.height())/h;
			    var height =  container.height();
			   	var left = -Math.ceil(Math.abs((container.width()-width)/2));
			    var top = 0;
		    }else{
		    	console.log(ratio_1+">"+ratio_2)
		    	var width = container.width();
			    var height = (h*container.width())/w;
			   	var left = 0;
			    var top = -Math.ceil(Math.abs((container.height()-height)/2));
		    }
		break;
		case "portrait-landscape":
		case "square-landscape":
		case "portrait-square":
		    var width = (w*container.height())/h;
		    var height =  container.height();
		   	var left =  -Math.ceil(Math.abs((container.width()-width)/2));
		    var top = 0;
		break;
		case "landscape-portrait":
		case "square-portrait":
		case "landscape-square":
            var width = container.width();
		    var height = (h*container.width())/w;
		   	var left = 0;
		    var top = -Math.ceil(Math.abs((container.height()-height)/2));
        break;
		case "square-square":
		    var width = container.width();
			var height =  container.height();
			var left = 0;
			var top = 0;
		break;
	}

	element.css({"width" : width+"px", "height" : height+"px", "left" : left+"px", "top" : top+"px"});

	/*//if(ratio_1 > ratio_2){
		var playerW = (100*container.data("width"))/container.data("height");//
		var playerH =  100;//(100*container.data("height"))/container.data("width");//(container.data("height")*container.width())/container.data("width");
		var playerL = 0;
		var playerT = -Math.ceil(Math.abs((100-playerH)/2));
	}else{
		var playerW =  100;//(100*container.data("width"))/container.data("height");//(container.data("width")*container.height())/container.data("height");
        var playerH =   (100*container.data("height"))/container.data("width");//
        var playerL = -Math.ceil(Math.abs((100-playerW)/2));
		var playerT= 0;
	}*/	
}





function bg_video(){
	$(".bg-video").each(function(){
		var vide = $(this).video();
		var video = $(this);
			/*$.ajax({
				url: "https://noembed.com/embed?url="+video.data("url"),
				success: function(response){
					vide.attr("data-width", response.width).attr("data-height", response.height);
				},
				dataType: "json"
		    });*/
		    var user_action = video.data("user-action");
		    var btn = video.find(".btn-play-toggle");
		    video
            .on('videoready', function (event, data) {
            	var w = video.width();
				var h = video.height();
				var player = video.find('iframe');
				var playerW = video.data("width");
				var playerH = video.data("height");

				/*var layout = video.data("layout");
				if(IsBlank(layout)){
				  layout = "fit";
				}
				switch(layout){
					case "fill":
					    video.addClass("bg-video-fill");
					    $(window).on("resize", function(){
					    	fill_element(video, video.find('iframe'), video.data("width"), video.data("height"));
						}).trigger("resize");
					break;
					case "fit" :
					    video.addClass("bg-video-fit");
						$(window).on("resize", function(){
							var videoW = video.width();
							var playerHN = Math.ceil(playerH*videoW/playerW);
							player.css({"position":"relative!important", "width" : "100%", "height" : playerHN+"px"});
						}).trigger("resize");
					break;
				}*/

        		if(video.data("muted")){
		           video.video("mute"); 
        		}
        	    if(video.data("autoplay")){
				   video.video("play");
				}
				if(user_action){
					btn
					.on("click", function(e){
						e.preventDefault();
						video.removeClass("user-pause").addClass("user-play");
						video.video('play');
					});
				}
        		//$(window).trigger("resize");
        	})
        	.on('videoplay', function (event, data) {
        		video.removeClass("loading").addClass("playing").addClass("ready").removeClass("paused");
        		video
				.on("click",function(e){
					e.preventDefault();
					if(video.hasClass("playing")){
				       video.removeClass("user-play").addClass("user-pause");
					   video.video('pause');						
					}
				});
	        	//$(window).trigger("resize");
        	})
        	.on('videopause', function (event, data) {
                video.removeClass("playing").addClass("paused");
                /*video
				.on("click", function(){
					video.removeClass("user-pause").addClass("user-play");
					video.video('play');
				});*/
        	})
        	.on('videoend', function (event, data) {
        		video.removeClass("playing");
        		video_container.removeClass("user-play").removeClass("user-pause");
        		if(video.data("loop")){
                   video.video('play');
        		}
        	});
	})
}

function bg_video_file(){
	$(".bg-video-file").each(function(){
		var video = $(this);
		var is_slide = video.closest(".swiper-slide").length>0?true:false;
		var file = video.attr("data-video-bg").replace("."+getFileExtension(video.attr("data-video-bg")),"");
		var movie_data = {mp4:file};
		var movie_options = video.attr("data-video-options");
		var poster_extension = "";
		if(!IsBlank(video.attr("data-video-poster"))){
			var poster_extension = getFileExtension(video.attr("data-video-poster"));
			movie_data["poster"] = video.attr("data-video-bg").replace("."+poster_extension,"");
			movie_options += ",posterType:"+poster_extension;
		}
		var ply = video.vide(movie_data, movie_options);
	    var video_container = is_slide?video.closest(".swiper-slide"):video;
		var player = $($(this).data('vide').getVideoObject())[0];
		var btn = video.find(".btn-play-toggle");
		var user_action = video.data("user-action");
		console.log(ply)
		console.log(player)
		/*var w = player.videoWidth;
	    var h = player.videoHeight;
	    var ratio = get_aspectRatio(w,h);
	        ratio = ratio[0] / ratio[1];
	    video.attr("data-ratio", ratio);*/
	    //var play_button = video_container.find("a#btn-play-"+video.data("index"));
	    //var play_button = video_container.find(".btn-play-toggle");


	    if(Boolean(video.data("autoplay"))){
            video_container.addClass("playing").addClass("user-play").removeClass("loading");
            video.muted = true;
	    }else{
	    	video_container.addClass("paused");
	    }
	                
	    /*if(user_action){
		    play_button
			.on("click",function(e){
		        e.preventDefault();
		        if(video_container.hasClass("playing")){
                    player.pause();
		        }else{
		        	player.play();
		        }
		        //video_container.addClass("playing").removeClass("paused");
			});
		}*/

		player.addEventListener('loadeddata', function(e){
			video.addClass("ready")
			video.closest(".loading").removeClass("loading");
			
			player
			.onplay = function() {
				video_container.addClass("playing").removeClass("paused");
	            if(user_action){
	                video_container
				    .on("click",function(){
				    	video_container.removeClass("user-play").addClass("user-pause");
					    player.pause();
		   		  	    $(this).unbind("click");
					});
	            }
			}

			player
			.onpause =  function() {
				video_container.addClass("paused").removeClass("playing");
				if(user_action){
					video_container
					.on("click",function(){
						video_container.removeClass("user-pause").addClass("user-play");
						player.play();
					})
				}
				/*if(video.data("autoplay")){
				    player.play();
			    }else{
			        player.pause();
					player.currentTime=0;
			    }*/
			}

			if(user_action){
				btn
				.on("click", function(e){
					e.preventDefault();
					video_container.removeClass("user-pause").addClass("user-play");
					player.play();
				});
			}

		});
			
		player.addEventListener('ended', function(e){
			player.pause();
			player.currentTime = 0;
			if(video.data("autoplay")){
			    player.play();
		    }
			if(user_action){
				video_container.removeClass("user-play").removeClass("user-pause");
				video_container.unbind("click");
		    }
		});
	});
}

function video_block(){
	$(".wp-block-video").each(function(){
		var container = $(this);
		var video = container.find("video");
		var video_control = true;
		var autoplay = video.attr("autoplay");
		if(typeof autoplay !== "undefined"){
		   video_control = false;
		}
		if(video_control){
			var player = video[0];
			$(this).on("click", function(e){
				e.preventDefault();
				if(container.hasClass("playing")){
				    player.pause();
				    container.addClass("paused").removeClass("playing");
				}else{
				    player.play();
				    container.addClass("playing").removeClass("paused");				
				}
			});
			player.addEventListener('ended',function(){
			    player.load();
			    container.addClass("paused");    
			}, false);
			container.addClass("controllable paused"); 			
		}
	});
}

/*
 *
 *	 $('.scroll-view').allLazyLoaded(function(){
 *		console.log(this)
 *	});

 *	 $('.scroll-view').allLazyLoaded().on('containerlazyloaded', function(){
 *		console.log(this)
 *	});
 */
jQuery.fn.allLazyLoaded = function(fn){
    if(this.length){
        var loadingClass, toLoadClass;
        var $ = jQuery;
        var isConfigured = function(){
            var hasLazySizes = !!window.lazySizes;

            if(!loadingClass && hasLazySizes){
                loadingClass = '.' + lazySizes.cfg.loadingClass;
                toLoadClass = '.' + lazySizes.cfg.lazyClass;
            }

            return hasLazySizes;
        };

        var isComplete = function(){
            return !('complete' in this) || this.complete;
        };

        this.each(function(){
            var container = this;
            var testLoad = function(){

                if(isConfigured() && !$(toLoadClass, container).length && !$(loadingClass, container).not(isComplete).length){
                    container.removeEventListener('load', rAFedTestLoad, true);
                    if(fn){
                        fn.call(container, container);
                    }
                    $(container).trigger('containerlazyloaded');
                }
            };
            var rAFedTestLoad = function(){
                requestAnimationFrame(testLoad);
            };

            container.addEventListener('load', rAFedTestLoad, true);
            rAFedTestLoad();
        });
    }
    return this;
};