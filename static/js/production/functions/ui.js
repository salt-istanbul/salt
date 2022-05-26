function _confirm(title, msg, size, className, btn_confirm, btn_cancel, callback){ // ***new*** added title
	    var options = {
       	    	className: "modal-confirm nodal-alert text-center ",
	       	    message : ".",
	       	    buttons: {
			        cancel: {
			            label: 'No',
			            className: 'btn-outline-danger btn-extend pull-right '
			        },
			        confirm: {
			            label: 'Yes',
			            className: 'btn-success btn-extend pull-left '
			        }
			    }
		}
		if(!IsBlank(title)){
			options["title"] = title;
		}
		if(!IsBlank(msg)){
			options["message"] = msg;
		}else{
			options["className"] += "modal-alert-textless ";
		}
		if(!IsBlank(size)){
			options["size"] = size;
		}
		if(!IsBlank(className)){
			options["className"] = className;
		}
		if(!IsBlank(btn_confirm)){
			options["buttons"]["confirm"]["label"] = btn_confirm;
		}
		if(!IsBlank(btn_cancel)){
			options["buttons"]["cancel"]["label"] = btn_cancel;
		}
		if(!IsBlank(callback)){
			options["callback"] = function(result){ 
									    if(result){
									    	callback(result);
									    }
								  }
		}
        var modal = bootbox.confirm(options);
        if(IsBlank(title)){
           modal.find(".modal-header").remove();
        }
        if(IsBlank(msg)){
           modal.find(".modal-body").remove();
        }
        return modal;

}
function _alert(title, msg, size, className, btn_ok, callback){
	    var options = {
       	    	className: "modal-alert text-center ",
       	    	message : ".",
	       	    buttons: {
		       	    	ok : {
						    label: 'OK',
						    className: 'btn-outline-success btn-extend'
						}
				}
		}
		if(!IsBlank(title)){
			options["title"] = title;
		}
		if(!IsBlank(msg)){
			options["message"] = msg;
		}else{
			options["className"] += "modal-alert-textless";
		}
		if(!IsBlank(size)){
			options["size"] = size;
		}
		if(!IsBlank(className)){
			options["className"]  += " "+className;
		}
		if(!IsBlank(callback)){
			options["callback"] = function(){ 
									    	callback();
								  }
		}
		if(!IsBlank(btn_ok)){
			options["buttons"]["ok"]["label"] = btn_ok;
		}
        return bootbox.alert(options)
}
function _prompt(){
	var dialog = bootbox.dialog({
	    title: 'A custom dialog with buttons and callbacks',
	    message: "<p>This dialog has buttons. Each button has it's own callback function.</p>",
	    size: 'large',
	    buttons: {
	        cancel: {
	            label: "I'm a cancel button!",
	            className: 'btn-danger',
	            callback: function(){
	                console.log('Custom cancel clicked');
	            }
	        },
	        noclose: {
	            label: "I don't close the modal!",
	            className: 'btn-warning',
	            callback: function(){
	                console.log('Custom button clicked');
	                return false;
	            }
	        },
	        ok: {
	            label: "I'm an OK button!",
	            className: 'btn-info',
	            callback: function(){
	                console.log('Custom OK clicked');
	            }
	        }
	    }
	});
}


function modal_confirm(){
	var token_init = "modal-confirm-init";
    $("[data-toggle='confirm']").unbind("click").on("click", function(e){
       	e.preventDefault();
       	var url = $(this).attr("href");
       	var title = $(this).data("confirm-title");
       	var message = $(this).data("confirm-message");
       	var size = $(this).data("confirm-size");
       	var classname = $(this).data("confirm-classname");
       	var btn_ok = $(this).data("confirm-btn-ok");
       	var btn_cancel = $(this).data("confirm-btn-cancel");
       	var _callback = $(this).data("confirm-callback");
       	var callback = function(){};
       	if(IsUrl(url)){
	       	var callback = function(){
	       		$("body").addClass("loading");
	       	    window.location.href = url;
	       	}       	    	
       	}else if(!IsBlank(_callback)){
	       	var callback = function(){
	       	    eval(_callback)
	       	}
       	}
       	_confirm(title, message, size, classname, btn_ok, btn_cancel, callback);
    });	
}
function modal_alert(){
	var token_init = "modal-alert-init";
    $("[data-toggle='alert']").on("click", function(e){
       	e.preventDefault();
       	var url = $(this).attr("href");
        var title = $(this).data("alert-title");
        var message = $(this).data("alert-message");
        if(!IsBlank(message)){
	        if(message.indexOf("#")==0){
	           message = $(message).html();
	        }        	
        }
        var size = $(this).data("alert-size");
        var btn_ok = $(this).data("alert-btn-ok");
        var classname = $(this).data("alert-classname");
        var _callback = $(this).data("alert-callback");
        if(IsUrl(url)){
	       	var callback = function(){
	       		$("body").addClass("loading");
	       	    window.location.href = url;
	       	}       	    	
       	}else if(!IsBlank(_callback)){
	       	var callback = function(){
	       	    eval(_callback)
	       	}
       	}
       	_alert(title, message, size, classname, btn_ok, callback);
    });	
}
function notification_alert(){
	var token_init = "notification-alert-init";
    $("[data-toggle='notification']").on("click", function(e){
       	e.preventDefault();
       	var target = $($(this).data("target"));
       	var message = $(this).data("notification-message");
       	target.prepend('<div class="alert alert-success text-center" role="alert">'+message+'</div>');
       	setTimeout(function(){
            target.find(".alert").first().fadeOut(500, function(){
               	  $(this).remove();
            })
       	}, 3000);
    });	
}




function init_swiper_video_slide(swiper, obj){

	if(obj.find(".swiper-video-url").not(".inited").length > 0){
        obj.find(".swiper-video-url").not(".inited").video();
        var ratio = 16 / 9;
        var video_slide = obj.find(".swiper-video-url").not(".inited");
            video_slide.attr("data-ratio", ratio);
       	var video_slide_container = video_slide.closest(".swiper-slide");
       	var play_button = video_slide_container.find("a#btn-play-"+video_slide.data("index"));
        var user_action = play_button.length>0?true:false;
        if(user_action){
	        play_button
			.on("click",function(e){
	            e.preventDefault();
	            video_slide.video("play");
	            video_slide_container.addClass("playing").removeClass("paused");
			});
		}
        video_slide
        .on('videoready', function (event, data) {
        	if(video_slide.data("muted")){
		       video_slide.video("mute"); 
            }
        	//if(video_slide.data("index") == 1){
        		if(video_slide.data("autoplay")){
        			swiper.autoplay.stop();
				    video_slide.video("play");
				}else{
				    if(swiper.params.autoplay.delay > 0 && !swiper.autoplay.running){
				        swiper.autoplay.start();
				        video_slide.video("pause");
				    }
				}
			//}else{
			//	video_slide.video("pause");
			//}
        	$(window).trigger("resize");
        })
        .on('videoplay', function (event, data) {
        	video_slide_container.addClass("playing").addClass("ready").removeClass("paused");

        	if($(swiper.slides).find(".slide-type-video-url.playing").length > 0){
        	    $(swiper.slides).find(".slide-type-video-url.playing").find(".swiper-video").video("pause");
        	}
            /*if(!is_mobile){
        		video_slide_container.one("webkitTransitionEnd otransitionend oTransitionEnd msTransitionEnd transitionend",
        		    function(event) {
        				video_slide_container.addClass("started");
        		});
        	}*/
        	if(swiper.params.autoplay.delay > 0 && swiper.autoplay.running){
        	   swiper.autoplay.stop();
        	}
                    
            if(user_action){
	            video_slide_container
	        	.on("click",function(){
	        		video_slide.video("pause");
	        	});
	        }
	        $(window).trigger("resize");
        })
        .on('videopause', function (event, data) {
            video_slide_container.unbind("click").removeClass("playing").addClass("paused");
        })
        .on('videoend', function (event, data) {
        	video_slide_container.removeClass("playing");
            if(video_slide.closest(".swiper-slide-active").length > 0){
        		swiper.slideNext();
        	   	/*if(swiper.params.autoplay.delay > 0 && !swiper.autoplay.running){
        	 	    swiper.autoplay.start();
        		}*/
        	}
        });
        video_slide.addClass("inited");
    }
    
    if(obj.find(".swiper-video-file").not(".inited").length > 0){
	    var video_slide = obj.find(".swiper-video-file").not(".inited")
			video_slide.vide(video_slide.attr("data-video-bg"), video_slide.attr("data-video-options"));
		var video_slide_container = video_slide.closest(".swiper-slide");

		var player = $(video_slide.data('vide').getVideoObject())[0];
		var w = player.videoWidth;
        var h = player.videoHeight;
        var ratio = get_aspectRatio(w,h);
            ratio = ratio[0] / ratio[1];
        video_slide.attr("data-ratio", ratio);
        var play_button = video_slide_container.find("a#btn-play-"+video_slide.data("index"));
        var user_action = play_button.length>0?true:false;
                
        if(user_action){
	        play_button
		    .on("click",function(e){
	            e.preventDefault();
	            player.play();
	            video_slide_container.addClass("playing").removeClass("paused");
			});
		}

		player.addEventListener('loadeddata', function(e){

			player
			.onplay = function() {
				video_slide_container.addClass("playing").removeClass("paused");
			    if(swiper.params.autoplay.delay > 0 && swiper.autoplay.running){
					swiper.autoplay.stop();
				}
                if(user_action){
                    video_slide_container
					.on("click",function(){
					    player.pause();
					    $(this).unbind("click");
					});
                }
				//$(window).trigger("resize");
			}

			player
			.onpause = function() {
				video_slide_container.addClass("paused").removeClass("playing");
				if(user_action){
					video_slide_container
					.on("click",function(){
						player.play();
					})
				}
			}
			
			//if(video_slide.data("index") == 1 && video_slide.data("autoplay")){
			if(video_slide.data("autoplay")){
				player.play();
				swiper.autoplay.stop();
			}else{
				player.pause();
				player.currentTime=0;
			}
		    //$(window).trigger("resize");
		});
		    
		player
		.addEventListener('ended', function(e){
			player.pause();
			player.currentTime = 0;
			//var video_data = video_slide.data('vide');
			//$(video_data.$wrapper).css("background-image", "url("+video_data.path.poster+".jpg)");
			if($(this).closest(".swiper-slide-active").length>0){
				if(swiper.params.autoplay.delay > 0 && !swiper.autoplay.running){
				   swiper.autoplay.start(); 
				}
				swiper.slideNext();
			}
			if(user_action){
				video_slide_container.unbind("click");
			}
		});

		video_slide.addClass("inited");
	}
}
function init_swiper_video(swiper){

	if($(swiper.$el).find(".swiper-video-url").not(".inited").length > 0 || $(swiper.$el).find(".swiper-video-file").not(".inited").length > 0){

		init_swiper_video_slide(swiper, $(swiper.slides[0]));

		swiper
		.on('slideChangeTransitionStart', function () {
					console.log("slideChangeStart");
					var swiper = this;
					var slide = $(swiper.slides[swiper.previousIndex]);
	                var video_slide = $(swiper.slides[(swiper.activeIndex > swiper.previousIndex?swiper.activeIndex:swiper.previousIndex)]);
					init_swiper_video_slide(swiper, video_slide);

					//slide.removeClass("paused");
					if(slide.find(".swiper-video-file").length>0){
					 	var player = $(slide.find(".swiper-video-file").data('vide').getVideoObject())[0];
						if(!player.paused){
							player.currentTime=0;
		                    player.pause();
						}
						if(swiper.params.autoplay.delay > 0 && !swiper.autoplay.running){
							swiper.autoplay.start();
						}
					}
					if(slide.find(".swiper-video-url").length>0){
					 	var player = slide.find(".swiper-video-url");
						if(player.video('playing')){
		                   player.video("stop")
						}
					}
				    slide.removeClass("paused").removeClass("ready").removeClass("playing"); 
	    })
	    .on('slideChangeTransitionEnd', function () {
					  console.log("slideChangeEnd");
					  var swiper = this;
					  var slide = $(swiper.slides[swiper.activeIndex]);

					  init_swiper_video_slide(swiper, slide);
					  
					      //slide.removeClass("paused");
					  if(slide.find(".swiper-video").length > 0){
						  if(slide.find(".swiper-video-file").length>0){
						  	 var player = $(slide.find(".swiper-video-file").data('vide').getVideoObject())[0];
						  	 player.currentTime=0;
						  	 if(slide.find(".swiper-video-file").data("autoplay")){
			                     player.play()
			                     if(swiper.params.autoplay.delay > 0 && swiper.autoplay.running){
									 swiper.autoplay.stop();
								 }
							 }else{
							 	 if(swiper.params.autoplay.delay > 0 && !swiper.autoplay.running){
									 swiper.autoplay.start();
								 }
							 }
							 //player.resize();
						  }
						  if(slide.find(".swiper-video-url").length>0){
						  	 var player = slide.find(".swiper-video-url");
						  	 if(player.data("autoplay")){
			                    player.video("play");
			                 }
						  }
						  $(window).trigger("resize");
					  }else{
						  if(swiper.params.autoplay.delay > 0 && !swiper.autoplay.running){
							 swiper.autoplay.start();
						  }
					  }
		})
		.on("touchStart", function(e) {
                    var index = this.activeIndex;
                    var slide = $(this.slides[index]);
                    if (slide.hasClass("slide-type-video") || slide.hasClass("slide-type-video-url")) {
                        var video = slide.find(".swiper-video");
                        if (video.hasClass("swiper-video-file")) {
                            var player = $(video.data('vide').getVideoObject())[0];
                            player.pause();
                        } else {
                            var player = video;
                            console.log(player)
                            player.video("pause");
                        }
                    }
        })
        .on("touchEnd", function(e) {
                    var index = this.activeIndex;
                    var slide = $(this.slides[index]);
                    if (slide.hasClass("slide-type-video") || slide.hasClass("slide-type-video-url")) {
                        var video = slide.find(".swiper-video");
                        if (video.hasClass("swiper-video-file")) {
                            var player = $(video.data('vide').getVideoObject())[0];
                            player.play();
                        } else {
                            var player = video;
                            player.video("play");
                        }
                    }
                    /*if($(this.$el).find("[data-bg-check]").length > 0){
                       BackgroundCheck.refresh();
                    }*/
        });

		$(window)
	    .on("resize", function(){
			    	var video_slide_container = $(swiper.slides[swiper.activeIndex]);
			    	var video_slide = video_slide_container.find(".swiper-video");
			    	if(video_slide.hasClass("swiper-video-url") && video_slide.hasClass("swiper-video-bg")){
				    	var ratio = video_slide.data("ratio");
				        video_bg_fit(video_slide_container, video_slide, ratio);		    		
			    	}
		})
	    .trigger("resize");

	}
}

function init_swiper($obj){
	var token_init = "swiper-slider-init";
	if(!IsBlank($obj)){
	   if($obj.not("."+token_init).length > 0){
	   	  $(this).addClass(token_init);
          return init_swiper_obj($obj);
       };
	}else{
	    $(".swiper-slider").not("."+token_init).each(function() {
	    	$(this).addClass(token_init);
			init_swiper_obj($(this));
		});
	}
}

function init_swiper_obj($obj) {
  
        var effect = $obj.data("slider-effect");
        var navigation = Boolean($obj.data("slider-navigation")||false);
        var pagination = $obj.data("slider-pagination")||"";
        var pagination_thumbs = Boolean($obj.data("slider-pagination-thumbs")||false);
        var autoplay = Boolean($obj.data("slider-autoplay")||false);
        var delay = $obj.data("slider-delay")||(autoplay?5000:0);
        var loop = Boolean($obj.data("slider-loop")||false);
        var lazy = Boolean($obj.data("slider-lazy")||false);
        var zoom = Boolean($obj.data("slider-zoom")||false);
        var direction = IsBlank($obj.data("slider-direction"))||$obj.data("slider-direction")=="horizontal"?"horizontal":"vertical";
        var scrollbar = false;
        var scrollbar_el = {};
        if($obj.find(".swiper-scrollbar").length>0){
           scrollbar = true;
           scrollbar_el = $obj.find(".swiper-scrollbar");
        }else{
           scrollbar = Boolean($obj.data("slider-scrollbar")||false);
        }
        var slidesPerView = $obj.attr("data-slider-slides-per-view")||1;
        var slidesPerGroup = $obj.attr("data-slider-slides-per-view")||1;

        console.log($obj, effect, pagination, navigation, pagination_thumbs, autoplay, delay, loop, lazy);

        var breakpoints = {
            1399: {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 15
            },
            1199: {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 15
            },
            991: {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 15
            },
            767: {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 15
            },
            575: {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 15
            },
            0: {
                slidesPerView: 1,
                slidesPerGroup: 1,
                spaceBetween: 15
            }
        };

        var slidesPerView = slidesPerView||breakpoints["1599"]["slidesPerView"];
        var slidesPerGroup = slidesPerGroup||breakpoints["1599"]["slidesPerView"];

        var $breakpoints = $obj.data("slider-breakpoints");
        var $gaps = $obj.data("slider-gaps");

        if (!IsBlank($breakpoints)) {
        	console.log($breakpoints);
            if ($breakpoints.hasOwnProperty("xs")) {
                breakpoints["0"] = {
                    slidesPerView: $breakpoints.xs=="auto"?1:$breakpoints.xs,
                    slidesPerGroup: $breakpoints.xs
                }
                if(!IsBlank($gaps)){
	                if ($gaps.hasOwnProperty("xs")) {
	                    breakpoints["0"]["spaceBetween"] = $gaps.xs;
	                }                	
                }
            }
            if ($breakpoints.hasOwnProperty("sm")) {
                breakpoints["575"] = {
                    slidesPerView: $breakpoints.sm=="auto"?1:$breakpoints.sm,
                    slidesPerGroup: $breakpoints.sm
                }
                if(!IsBlank($gaps)){
	                if ($gaps.hasOwnProperty("sm")) {
	                    breakpoints["575"]["spaceBetween"] = $gaps.sm;
	                }
	            }
            }
            if ($breakpoints.hasOwnProperty("md")) {
                breakpoints["768"] = {
                    slidesPerView: $breakpoints.md=="auto"?1:$breakpoints.md,
                    slidesPerGroup: $breakpoints.md
                }
                if(!IsBlank($gaps)){
	                if ($gaps.hasOwnProperty("md")) {
	                    breakpoints["768"]["spaceBetween"] = $gaps.md;
	                }
	            }
            }
            if ($breakpoints.hasOwnProperty("lg")) {
                breakpoints["991"] = {
                    slidesPerView: $breakpoints.lg=="auto"?1:$breakpoints.lg,
                    slidesPerGroup: $breakpoints.lg
                }
                if(!IsBlank($gaps)){
	                if ($gaps.hasOwnProperty("lg")) {
	                    breakpoints["991"]["spaceBetween"] = $gaps.lg;
	                }
	            }
            }
            if ($breakpoints.hasOwnProperty("xl")) {
                breakpoints["1199"] = {
                    slidesPerView: $breakpoints.xl=="auto"?1:$breakpoints.xl,
                    slidesPerGroup: $breakpoints.xl
                }
                if(!IsBlank($gaps)){
	                if ($gaps.hasOwnProperty("xl")) {
	                    breakpoints["1199"]["spaceBetween"] = $gaps.xl;
	                }
	            }
            }
            if ($breakpoints.hasOwnProperty("xxl")) {
                breakpoints["1399"] = {
                    slidesPerView: $breakpoints.xxl=="auto"?1:$breakpoints.xxl,
                    slidesPerGroup: $breakpoints.xxl
                }
                if(!IsBlank($gaps)){
	                if ($gaps.hasOwnProperty("xxl")) {
	                    breakpoints["1399"]["spaceBetween"] = $gaps.xxl;
	                }
	            }
            }
        }else{
        	if($gaps){
        		var spaceBetween = $gaps;
        	}
        }

        if (pagination_thumbs){
        	if($obj.find(".swiper-thumbs").length == 0) {
        	   $obj.append("<div class='swiper-thumbs'></div>");
        	}
            var galleryThumbs = new Swiper($obj.find(".swiper-thumbs")[0], {
                spaceBetween: 10,
                slidesPerView: 10,
                freeMode: true,
                watchSlidesVisibility: true,
                watchSlidesProgress: true,
                slideToClickedSlide:true
            });
        } 

        var options = {
            //cssMode: true,
            slidesPerView: 1,
            spaceBetween: IsBlank(spaceBetween)?0:spaceBetween,
            resistance: '100%',
            resistanceRatio: 0,
            watchOverflow: true,
            grabCursor: true,
            centeredSlides: false,
            watchSlidesVisibility: true,
            centerInsufficientSlides : true,
            preventInteractionOnTransition : true,
            speed: 750,
            //breakpoints: breakpoints,
            autoplay : false,
            on: {
                init: function() {
                    //$.fn.matchHeight._update();
                    var slider = this;
                    init_swiper_video(slider);
                    $(".link-initial a").on("click", function() {
                        console.log($("#home").next("section").attr("id"))
                        root.ui.scroll_to("#" + $(".main-content").find("section").first().attr("id"));
                    });
                    /*if ($(slider.$el).find(".lazyload").length > 0) {
                        if (slider.params.autoplay.delay > 0 ) {
                            slider.autoplay.stop();
                        }
                        if ($(slider.$el).find(".swiper-image").length > 0) {
                        } else {
                            $(slider.$el).removeClass("loading-hide");
                            if (autoplay.delay > 0 ) {
	                            slider.autoplay.start();
	                        }
                        }
                    } else {
                        $(slider.$el).removeClass("loading-hide");
                    }*/
                    //fade in
                    if ($(slider.$el).hasClass("fade")) {
                        $(slider.$el).addClass("show");
                    }
                    //remove if parent has loading
                    if ($(slider.$el).closest(".loading").length > 0) {
                        $(slider.$el).parent().removeClass("loading");
                    }
                    //footer visibility
                    if ($(slider.$el).closest(".card").length > 0) {
                        if ($(slider.$el).closest(".card").find(">.card-footer .swiper-pagination").length > 0) {
                            if (slider.slides.length <= slider.params.slidesPerView) {
                                $(slider.$el).closest(".card").find(">.card-footer").addClass("d-none");
                            } else {
                                $(slider.$el).closest(".card").find(">.card-footer").removeClass("d-none");
                            }
                        }
                    }
                    //stop autoplay on hover
                    if (autoplay) {
                    	 var slider = this;
	                    $(slider.$el)
	                    .on('mouseenter', function(e){
	                    	console.log(slider);
	                    	console.log(slider.autoplay);
						    slider.autoplay.stop();
						})
						.on('mouseleave', function(e){
						    slider.autoplay.start();
						});
                    }
                    if($(this.$el).find("[data-bg-check]").length > 0){
                        bg_check();
                    }
                },
                loopFix : function(){
                   lazyLoadInstance.update();
                },

                slideChangeTransitionStart: function (e) {
                	console.log($(e.slides[e.activeIndex]))
                    if($(e.slides[e.activeIndex]).find(".swiper-container").length > 0){
                    	var nested = $(e.slides[e.activeIndex]).find(".swiper-container")[0].swiper;
                    	if(typeof nested !== "undefined"){
                    		nested.autoplay.stop();
                    	    nested.slideTo(0);
                    	}
                    }
                },
                
                slideChangeTransitionEnd: function (e) {
                    if($(this.$el).find("[data-bg-check]").length > 0){
                       BackgroundCheck.refresh();
                    }
                },
                resize: function() {
                    if ($(this.$el).closest(".card").length > 0) {
                        if ($(this.$el).closest(".card").find(">.card-footer .swiper-pagination").length > 0) {
                            if (this.slides.length <= this.params.slidesPerView) {
                                $(this.$el).closest(".card").find(">.card-footer").addClass("d-none");
                            } else {
                                $(this.$el).closest(".card").find(">.card-footer").removeClass("d-none");
                            }
                        }
                        if($(this.$el).find("[data-bg-check]").length > 0){
                        	BackgroundCheck.refresh();
                        }
                    }
                },
                slidesGridLengthChange: function() {
                    //footer visibility
                    if ($(this.$el).closest(".card").length > 0) {
                        if ($(this.$el).closest(".card").find(">.card-footer .swiper-pagination").length > 0) {
                            if (this.slides.length <= this.params.slidesPerView) {
                                $(this.$el).closest(".card").find(">.card-footer").addClass("d-none");
                            } else {
                                $(this.$el).closest(".card").find(">.card-footer").removeClass("d-none");
                            }
                        }
                    }
                    if(this.params.slidesPerView == "auto"){
                    	//this.params.freeMode = true;
                    	if(this.params.loop){
	                       this.params.loopedSlides = this.slides.length;
                    	}
                    }else{
                    	//this.params.freeMode = false;
                    }
                }
            }
        };

        if (!IsBlank($breakpoints)) {
        	options["breakpoints"] = breakpoints;
        	if(slidesPerView){
        		options["slidesPerView"] = slidesPerView;
        		options["slidesPerGroup"] = slidesPerGroup;
        	}
        }

        if(scrollbar){
            if(scrollbar_el.length > 0){
           	    options["scrollbar"] = {
		          el: scrollbar_el[0]
		        }
           }else{
           	    if ($obj.parent().find(".swiper-scrollbar").length > 0) {
		            options["scrollbar"] = {
			          el: $obj.parent().find('.swiper-scrollbar')[0]
			        }
	            }else{
	            	$obj.append("<div class='swiper-scrollbar'></div>");
	            	options["scrollbar"] = {
			          el: $obj.find('.swiper-scrollbar')[0]
			        }
	            }
           }
        }

        if (navigation) {
	        var prevEl = $obj.find('.swiper-button-prev')[0];
	        var nextEl = $obj.find('.swiper-button-next')[0];
	        if ($obj.parent().find(".swiper-button-prev").length > 0) {
	            prevEl = $obj.parent().find('.swiper-button-prev')[0];
	            nextEl = $obj.parent().find('.swiper-button-next')[0];
	        }
	        if ($("body").hasClass("rtl")) {
	            options["navigation"] = {
	                nextEl: prevEl,
	                prevEl: nextEl
	            }
	        } else {
	            options["navigation"] = {
	                prevEl: prevEl,
	                nextEl: nextEl
	            }
	        }
	    }
        if(!IsBlank(pagination)) {
            var pagination_obj = $obj.find('.swiper-pagination')[0];
            if ($obj.closest(".card").length > 0) {
                if ($obj.closest(".card").find(">.card-footer .swiper-pagination").length > 0) {
                    pagination_obj = $obj.closest(".card").find(">.card-footer .swiper-pagination")[0];
                }
            }
            options["pagination"] = {
                el: pagination_obj,
                clickable: true,
                type: pagination
            }
            if(pagination == "custom"){
            	options["pagination"]["renderCustom"] =  function (swiper, current, total) {
			      return ('0' + current).slice(-2) + '/' + ('0' + total).slice(-2);
			    }
            }
        };
        if (pagination_thumbs) {
            options["thumbs"] = {
                swiper: galleryThumbs
            }
        }
        if (autoplay || delay) {
            options["autoplay"] = {
            	enabled: autoplay,
                delay: delay
            }
        }
        if (loop) {
            options["loop"] = loop;
        }
        switch (effect) {
            case "fade":
                options["effect"] = effect;
                options["fadeEffect"] = {
                    crossFade: false
                }
                break;
            case "coverflow":
                options["effect"] = effect;
                options["coverflowEffect"] = {
                    rotate: 30,
                    slideShadows: false
                }
                break;
            case "flip":
                options["effect"] = effect;
                options["flipEffect"] = {
                    rotate: 30,
                    slideShadows: false
                }
                break;
            case "cube":
                options["effect"] = effect;
                options["cubeEffect"] = {
                    slideShadows: false
                }
                break;
        }
        if (zoom) {
            options["zoom"] = zoom;
        }
        if ($("body").hasClass("rtl")) {
            $obj.attr("dir", "rtl");
        }
        if(lazy){
        	options["preloadImages"] = false;
            options["lazy"] = {
			    loadPrevNext: true,
			}
        }
        if(direction){
        	options["direction"] = direction;
        }

        var dataAttr = $obj.data();
	    if(dataAttr){
	       	$.extend(options, dataAttr);
	    }
	    //console.log(options);

        var swiper = new Swiper($obj[0], options);
        console.log(swiper)
        return swiper;
}

function scrollable_init(){
	var token_init = "scrollable-init";
    $(".scrollable").not("."+token_init).each(function(e){
	    $(this).addClass(token_init);
        SimpleScrollbar.initEl($(this)[0]);
    });	
}

function star_rating_readonly(){
	var token_init = "star-rating-readonly-init";
    if($(".star-rating-readonly-ui").not("."+token_init).length>0){
        $(".star-rating-readonly-ui").not("."+token_init).each(function(){
           	$(this).addClass(token_init);
            var stars = $(this).data("stars") || 5;
            var value = $(this).data("value");
           	$(this).html(get_star_rating_readonly(stars, value, "", "", "" ));
        });
	}
}
function get_star_rating_readonly($stars, $value, $count, $star_front, $star_back ){
    $stars = parseInt($stars);
    $stars = IsBlank($stars)||isNaN($stars)?5:$stars;
    $value = parseFloat($value);
    if(typeof $count === "undefined"){
      $count="";
    }else{
      if($count>0){
         $count='<span class="count">('+$count+')</span>';
      }else{
         $count = "";
      }
    }
    var $className = "";
    if($value == 0 ){
       //return "";*  
       $className = " not-reviewed ";
    }
    $value = IsBlank($value)||isNaN($value)?0:$value;
    $star_front = IsBlank($star_front)?"fas fa-star":$star_front;
    $star_back = IsBlank($star_back)?"fas fa-star":$star_back;
    var $percentage = (100 * $value)/$stars;
    var $code ='<div class="star-rating-custom star-rating-readonly '+$className+'" title="' + $value + '">' +
                    '<div class="back">';
                            for ($i = 1; $i <= $stars; $i++) {
                                 $code += '<i class="'+$star_back+'" aria-hidden="true"></i>';
                            };
                      $code += '<div class="front" style="width:'+$percentage+'%;">';
                                   for ($i = 1; $i <= $stars; $i++) {
                                        $code += '<i class="'+$star_front+'" aria-hidden="true"></i>';
                                   };
                      $code += '</div>' +
                    '</div>' +
                    '<div class="sum">'+$value.toFixed(1) + $count +'</div>' +
               '</div>';
    return $code;
}

function btn_loading(){
	var token_init = "btn-loading-init";
	$(".btn-loading").not("."+token_init).each(function(){
        $(this)
        .on("click", function(e){
            $(this).addClass("loading disabled");
        })
        .addClass(token_init);
    });	
}
function btn_loading_page(){
	var token_init = "btn-loading-page-init";
	$(".btn-loading-page").not("."+token_init).each(function(){
        $(this)
        .on("click", function(e){
        	if(IsUrl($(this).attr("href"))){
        		$("body").addClass("loading-process");
        	}
        })
        .addClass(token_init);
	});
}
function btn_loading_page_hide(){
	var token_init = "btn-loading-page-hide-init";
	$(".btn-loading-page-hide").not("."+token_init).each(function(){
        $(this)
        .on("click", function(e){
        	if(IsUrl($(this).attr("href"))){
			    $("body").addClass("loading-hide");
			}
        })
        .addClass(token_init);
	});
}
function btn_loading_self(){
	var token_init = "btn-loading-self-init";
	$(".btn-loading-self").not("."+token_init).each(function(){
        $(this)
        .on("click", function(e){
            $(this).addClass("loading disabled");
        })
        .addClass(token_init);
    });	
}
function btn_loading_parent(){
	var token_init = "btn-loading-parent-init";
	$(".btn-loading-parent").not("."+token_init).each(function(){
        $(this)
        .on("click", function(e){
            $(this).parent().addClass("loading-process disabled");
        })
        .addClass(token_init);
    });	
}
function btn_ajax_method(){ /// ***new*** updated function
	var token_init = "btn-ajax-method-init";
	$("a[data-method]").not("."+token_init).each(function(){
		var $obj = $(this);
        $obj
        .addClass(token_init)
        .on("click", function(e){
        	e.preventDefault();
		    var $data = $obj.data();
        	var $form = {};
        	if($data.hasOwnProperty("form")){
               $data["form"] = $($data["form"])
        	}
			delete $data["method"];
			var callback = function(){
	            var query = new ajax_query();
				    query.method =  $obj.data("method");
				    query.vars   = $data;
					query.form   = $form;
					query.request();				
			}
			if($data["confirm"]){
				var confirm_message = $data["confirm-message"];
				if(IsBlank(confirm_message)){
                   confirm_message =  "Are you sure?";
				}
				var modal = _confirm(confirm_message, "", "md", "modal-confirm", "Yes", "No", callback);
			}else{
                callback();
			}
        })
    });
}

function btn_pay_now(){
	var token_init = "btn-pay-now-init";
	$(".btn-pay-now").not("."+token_init).each(function(e){
		e.preventDefault();
		var vars =  {
	        offer_id : $(this).data("offer-id"),
	    };
        var query = new ajax_query();
	    	query.method = "pay_now";
	    	query.vars = vars;
			query.request();
    });
}

function btn_forgot_password(){
	var token_init = "btn-forgot-password-init";
	$(".btn-forgot-password").not("."+token_init).each(function(e){
		var dialog = bootbox.dialog({
			title: 'Forgot Password',
			message: //'<p>We will send a password reset link to your e-mail address.</p>' +
				'<form id="lostPasswordForm" class="form form-validate" autocomplete="off" method="post" action="" data-ajax-method="create_lost_password">' +
					'<div id="message"></div>' +
					'<div class="form-group form-group-slim">' +
						'<label class="form-label form-label-lg">Email Address</label>' +
						'<input class="form-control form-control-lg" type="email" name="username" placeholder="Email Address" required/>' +
					'</div>' +
				'</form>',
				size: 'md',
				class : "modal-lost-password modal-fullscreen",
				buttons: {
					cancel: {
						label: "Cancel",
						className: 'btn-danger',
						callback: function(){
							console.log('Custom cancel clicked');
						}
					},
					ok: {
						label: "Send my password",
						className: 'btn-info',
						callback: function(){
							var form	= $("form#lostPasswordForm");
							var vars = {
								user_login:	form.find("[name='username']").val()															
							};
							this.find(".modal-content").addClass("loading-process");
							var query = new ajax_query();
								query.method = "lost_password";
								query.vars   = vars;
								query.form   = $(form);
								query.request();
								return false;
						}
					}
				}
			});
    });
}



function selectpicker_change(){
	$(".selectpicker.selectpicker-url").on("change",function(e){
			var url = $(this).val();
			if(IsUrl(url)){
				$("body").addClass("loading");
				window.location.href = url;
			}else{
				url = $(this).find("option[value='"+url+"']").data("value");
				if(IsUrl(url)){
					$("body").addClass("loading");
					window.location.href = url;
				}
			}
	});

	$(".selectpicker.selectpicker-url-update").on("change",function(){
            var url = $(this).val();
            var title = $(this).find("option[value='"+url+"']").text();
            window.history.pushState('data', title, url);
            document.title = title;
	});

	$(".selectpicker.selectpicker-country").each(function(){
			$(this).on("change",function(){
	            var vars =  {
	            	          id : $(this).val(),
	            	          state : $(this).data("state")
	            	        };
	            var query = new ajax_query();
				    query.method = "get_states";
				    query.vars = vars;
				    query.request();
			})
	}).trigger("change");
}


//new
function btn_card_option(){
	var token_init = "btn-card-option-init";
	$(".btn-card-option").find("input[checked]").parent().addClass("active").closest(".card").addClass("active");
	$(".btn-card-option").not("."+token_init).each(function(){
        $(this)
        .on("click", function(e){
        	$(this).addClass("active");
        	var card = $(this).closest(".card");
        	    card.parent().find(".card.active").removeClass("active").find(".btn-card-option.active").removeClass("active");
                card
                .addClass("active")
                .find("input[type='radio'], input[type='checkbox']").prop("checked", true);
        })
        .addClass(token_init);
    });		
}
//new
function btn_list_group_option(){
	var token_init = "btn-list-group-option-init";
	$(".list-group-options").each(function(){
		var list = $(this);
		list.find(".list-group-item").not(".list-group-item-content").each(function(){
            var option = $(this);
                var input = option.find("input");
                if(input.is(":checked")){
                   option.addClass("active");
                }
                option.on("click", function(e){
                	//e.preventDefault();
                	if(input.attr("type") == "radio"){
                	   input.prop("checked", true);
                	   list.find(".active").removeClass("active");
                	   option.addClass("active");
                	}else{
                	   if(input.is(":checked")){
                	   	  input.focus().prop("checked", false);
                	   	  option.removeClass("active");
                	   }else{
                	   	  input.focus().prop("checked", true);
                	   	  option.addClass("active");
                	   }
                	}
                });
		})
		list.addClass(token_init);
	})	
}



function getEvents(obj, calendar, month, year){
	var vars = {
	 	month : month,
	 	year  : year,
	 	date  : year+"-"+month
	 };
	 var objs = {
	 		obj      : obj,
	 		calendar : calendar
	 };
	 var query = new ajax_query();
		 query.method = "get_events";
  		 query.vars   = vars;
		 query.form   = {};
		 query.objs   = objs;
		 query.request();
}
function calendar(){
	var token_init = "calendar-init";
    if($(".calendar").not("."+token_init).length > 0){
    	var currentMonth = moment().format('MM');
		var currentYear = moment().format('YYYY');
		var nextMonth    = moment().add(1,'month').format('YYYY-MM');
	    $(".calendar").not("."+token_init).each(function(){
	    	eventsThisMonth: [ ];
	        var events_list=[];

	        var $calendar = $(this);
	            $calendar.addClass(token_init);
	       	var $template = $calendar.data("template");
	       	if(!IsBlank($template)){
	        	twig({
						href : host+"template/static/templates/"+$template+".twig",
						async : false,
						allowInlineIncludes : true,
						load: function(template) {
							moment.locale(root.lang);
							console.log(moment().calendar())
							$calendar.clndr({
								moment: moment,
							    render : function(data){
							  	        return template.render(data);
							    },
							    startWithMonth: moment(),
							    clickEvents: {
								    // fired whenever a calendar box is clicked.
								    // returns a 'target' object containing the DOM element, any events, and the date as a moment.js object.
								    click: function(target){
								    	  $(".popover").each(function(){
											 var id=$(this).attr("id");
											 $("[aria-describedby="+id+"]").popover("destroy"); 
										  });
										 
										  if(target.events.length) {
											 var today = new Date();
	                                             
										     var eventDate = new Date(target.events[0].date);
											 console.log(today+" = "+eventDate)
											  //if(eventDate<=today){
											     window.location.href=target.events[0].url;
											  //}
										  }
								    },
								    // fired when a user goes forward a month. returns a moment.js object set to the correct month.
								    nextMonth: function(month){ },
								    // fired when a user goes back a month. returns a moment.js object set to the correct month.
								    previousMonth: function(month){ },
								    // fired when a user goes back OR forward a month. returns a moment.js object set to the correct month.
								    onMonthChange: function(month){
								    	moment.locale("en");
								    	console.log(month)

								    	getEvents($calendar, this, month.locale('en').format('M'), month.locale('en').format('YYYY'));
								    },
								    // fired when a user goes to the current month/year. returns a moment.js object set to the correct month.
								    today: function(month){ }
								},
							    events: [],
								doneRendering: function(am){ 
									     /*var events=this.options.events;
									     console.log(this);
									     if(!IsBlank(events)){
								             for(var event in events){
												 var eventDay=events[event];
												 var obj=$(".calendar-day-"+eventDay.date);
												 obj.attr("id",eventDay.date.replaceAll("-","_"));
												 obj.attr("role","button");
												 obj.attr("data-content",eventDay.title);
												 obj.attr("data-trigger","focus");//"focus");
												 obj.attr("data-html","true");
												 obj.attr("data-container","body");
												 obj.attr("data-template",'<div class="popover text-xs" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>');
												 obj.on("mouseover",function(){
													$(this).popover("show") 
												 });
												 obj.on("mouseout",function(){
													$(this).popover("hide") 
												 });
											 }
										}*/
								},
								ready:function(aa){
									getEvents($calendar, this, currentMonth, currentYear)
								}
							});
						}
				});
	        }
	    });
	}
}



function readmore_js(){
    $(".readmore-js").each(function(){
    	var height = $(this).data("height") || 300;
    	if(IsBlank($(this).attr("id"))){
    		$(this).attr("id", generateCode(5));
    	}
        $(this).readmore({
            speed: 75,
            collapsedHeight: height,
            moreLink: '<a href="#" class="btn btn-link btn-slim float-right btn-more" style="display:inline-block;width:auto;margin-top:10px">Read more</a>',
            lessLink: '<a href="#" class="btn btn-link btn-slim float-right btn-less" style="display:inline-block;width:auto;margin-top:10px">Read less</a>',
            beforeToggle: function(trigger, element, expanded) {
	            console.log(element)
	            if(expanded){
	            	$('html,body').animate({ scrollTop: element.offset().top }, 75);
	            }
            }
        });
    });
}


function btn_favorite(){
    $(".btn-favorite").unbind("click").on("click", function(e){
		e.preventDefault();
		if($(this).hasClass("active")){
			favorites.remove($(this));
		}else{
			favorites.add($(this));
		}
	});
}

function stickyScroll(){
	var token_init = "sticky-scroll-init";
	if($(".stick-top").not("."+token_init).length > 0){
		var $options_tmp = stickyOptions;
        $(".stick-top").not("."+token_init).each(function(){
        	$(this).addClass(token_init);
            var $options = $options_tmp;
        	var $args = $options["assign"]($(this));
        	if(Object.keys($args).length>0){
        	   $options = nestedObjectAssign({}, $options, $args);
        	}
           	$(this).hcSticky($options);
           	console.log($options)
            $(this).hcSticky('update', $options);
        });
        //delete $options_tmp["assign"];
    }
}


function toast_notification($notification){
	        var text = "";
	        if(!IsBlank($notification.url)){
	        	text += "<a href='"+$notification.url+"' class='jq-toast-text-linked'>";
	        }
	        text += "<div class='jq-toast-text-wrapper'>";
	        if(!IsBlank($notification.sender.image)){
		       text += $notification.sender.image;
		    }
		    if(!IsBlank($notification.message)){
		       text += "<div class='jq-toast-text'>"+$notification.message;
		    }
		    	if(!IsBlank($notification.time)){
			       text += "<small class='jq-toast-text-date'>"+$notification.time+"</small>";
			    }
		    if(!IsBlank($notification.message)){
		       text += "</div>";
		    }
            text += "</div>";
	        if(!IsBlank($notification.url)){
	        	text += "</a'>";
	        }
            $.toast({
			    //heading: response[i].title,
			    text: text,
			    stack: 4,
			    position: 'bottom-left',
			    icon : false,
			    bgColor: '#fff',
                textColor: '#333',
                hideAfter: 6000,
                loaderBg: '#bf1e2d',
                showHideTransition : 'fade',
                beforeShow: function () {
			        $("body").addClass('toast-open');
			    },
			    afterShown: function () {
			    },
			    beforeHide: function () {
			    },
			    afterHidden: function () {
			        $("body").removeClass('toast-open');
			    }
			});
			/*myToast.update({
			    position: 'top-left',
			    stack : 1,
			    showHideTransition : 'slide'
			});*/
}


function ajax_paginate(){
	var token_init = "ajax-paginate-init";
    if($(".ajax-paginate").not("."+token_init).length>0){
        $(".ajax-paginate").not("."+token_init).each(function(){
        	var obj = $(this)
           	obj.addClass(token_init);
			//delete $data["method"];
			var btn = obj.find(".btn-next-page");
			var $data = getDataAttributes(obj);
			if(IsBlank($data.load) || typeof $data.load === "undefined"){
               $data["load"] = "button";
			}
			if($data.form){
			   $($data.form).find("[type='submit']").on("click", function(e){
			   	   e.preventDefault();
			   	   $($data.form).find("input[name='page']").val(1);
			   	   $(".list-cards").empty();
			   	   $($data.form).submit();
			   });
			}

			//btn.on("click", function(e){
			    	//e.preventDefault();
			function ajax_paginate_load(btn){
				    if(btn.hasClass("processing") || btn.hasClass("completed")){
				    	return;
				    }
				    btn.addClass("loading processing");
			    	
			    	var $data = getDataAttributes(obj);
			    	if($data.form){
			    	   var method = $($data.form).data("ajax-method");
			    	   ajax_hooks[method]["done"] = function(response, vars, form, objs){
			    	   	    var total = parseInt(response.data.total);
				    	   	var page = parseInt(response.data.page);
				    	   	var page_total = parseInt(response.data.page_total);
				    	   	var post_per_page = parseInt(response.data.post_per_page);
		    			    form.find("input[name='page']").val(page + 1);
		    			    if(response.data.page >= response.data.page_total){
						       btn.closest(".card-footer").addClass("d-none");
						       btn.addClass("completed").removeClass("loading processing");
						    }else{
						       btn.closest(".card-footer").removeClass("d-none");
						       btn.removeClass("loading processing");
						    }
						    if(btn.find(".item-left").length>0){
						       btn.find(".item-left").text(total - post_per_page*page);
                               //btn.find(".item-left").text(total - (page * Math.ceil(total/page_total)));
						    }
						    if(btn.hasClass("ajax-load-scroll")){
                               $(window).trigger("scroll");
							}
						}
			    	    $($data.form).submit();
			    	}else{
			            var query = new ajax_query();
						    query.method = obj.data("method");
						    query.vars = $data;
						    query.objs = {
						    	obj : obj
						    }
						    query.after = function(response, vars, form, objs){
						    	var total = parseInt(response.data.total);
				    	   	    var page = parseInt(response.data.page);
				    	   	    var page_total = parseInt(response.data.page_total);
				    	   	    var post_per_page = parseInt(response.data.post_per_page);
						    	obj.attr("data-page", page + 1);
						    	obj.attr("data-page-total", page_total);
						    	obj.attr("data-count", response.data.count);
						    	console.log(response.data.page +" == "+ response.data.page_total)
						    	if(response.data.page >= response.data.page_total){
							       btn.closest(".card-footer").addClass("d-none");
							       btn.addClass("completed").removeClass("loading processing");
							    }else{
							       btn.closest(".card-footer").removeClass("d-none");
							       btn.removeClass("loading processing");
							    }
							    if(btn.find(".item-left").length>0){
							       btn.find(".item-left").text(total - post_per_page*page);
	                               //btn.find(".item-left").text(total - (page * Math.ceil(total/page_total)));
							    }
							    if(btn.hasClass("ajax-load-scroll")){
                                   $(window).trigger("scroll");
							    }
						    }
							query.request();			    		
					}
			//});
		    }
			

			switch($data.load){
				case "button":
				case "":
				    btn.addClass("ajax-load-click")
				    btn.on("click", function(e){
			    	   e.preventDefault();
			    	   ajax_paginate_load($(this));
			        });
					if(btn.attr("data-init")){
					    btn.trigger("click");
					}
				break;
				case "scroll":
                    btn.addClass("ajax-load-scroll")
					$(window).scroll(function() {
				        if( btn.is(":in-viewport")) {
		                    ajax_paginate_load(btn);
				        }else{

				        }
				    }).trigger("scroll");
				break;
			}
		});
	}
}
