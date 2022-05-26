//(function ($) {
  jQuery.fn.hasAttr = function (attrName) {
		return (this.filter(function() {
			if(this.hasAttribute(attrName)){
			   return true;
			}
			/*if ( typeof $(this).attr(attrName) !== undefined ) {
				// note: this test does not distinguish between an element with no 'attrName' attribute
				// or an element with an 'attrName' equal to an empty string, both cases return false
				return true;
			}*/
			return false;
		}));
	};
//}(jQuery));

jQuery.expr[':'].Contains = function(a,i,m){
	return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
};

jQuery.fn.justtext = function() {   
    return $(this).clone()
            .children()
            .remove()
            .end()
            .text();
};

$.fn.textIsChanged = function(options) {
	var obj=this;
	var val=obj.html();
	var defaults = {
	   val: val,
	   callback : function(val){
					console.log("changed : "+val);   
				 }
	}
	options = jQuery.extend(defaults, options);
	var chk=setInterval(function() {
		if (obj.html() !== options.val) {
			clearInterval(chk)
			options.callback(obj.html());
		} 		
	},500);
};

function generateCode(codeLen){
	if(IsBlank(codeLen)){
		codeLen=5;
	}
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    for( var i=0; i < codeLen; i++ )
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    return text;
};

function onClassChange($obj, $class, $callback){
	var observer = new MutationObserver(function(mutations) {
	 mutations.forEach(function(mutation) {
	    if (mutation.attributeName === "class") {
	      var attributeValue = $(mutation.target).prop(mutation.attributeName);
	      var classes = attributeValue.split(/\s+/);
	      if(classes.indexOf($class) > -1 && typeof $callback === "function"){
	      	 eval($callback)();
	      	 console.log("Class attribute changed to:", attributeValue);
	      }
	      
	    }
	  });
	});
	observer.observe($obj[0], {
	  attributes: true
	});
}

// browser detect
var BrowserDetect = {
    init: function() {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";

	},
	searchString: function(data) {
		for (var i = 0; i < data.length; i++) {
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1) return data[i].identity;
			} else if (dataProp) return data[i].identity;
		}
	},
	searchVersion: function(dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index + this.versionSearchString.length + 1));
	},
	dataBrowser: [{
		string: navigator.userAgent,
		subString: "Chrome",
		identity: "Chrome"
	}, {
		string: navigator.userAgent,
		subString: "OmniWeb",
		versionSearch: "OmniWeb/",
		identity: "OmniWeb"
	}, {
		string: navigator.vendor,
		subString: "Apple",
		identity: "Safari",
		versionSearch: "Version"
	}, {
		prop: window.opera,
		identity: "Opera",
		versionSearch: "Version"
	}, {
		string: navigator.vendor,
		subString: "iCab",
		identity: "iCab"
	}, {
		string: navigator.vendor,
		subString: "KDE",
		identity: "Konqueror"
	}, {
		string: navigator.userAgent,
		subString: "Firefox",
		identity: "Firefox"
	}, {
		string: navigator.vendor,
		subString: "Camino",
		identity: "Camino"
	}, { // for newer Netscapes (6+)
		string: navigator.userAgent,
		subString: "Netscape",
		identity: "Netscape"
	}, {
		string: navigator.userAgent,
		subString: "MSIE",
		identity: "Explorer",
		versionSearch: "MSIE"
	}, {
		string: navigator.userAgent,
		subString: "Gecko",
		identity: "Mozilla",
		versionSearch: "rv"
	}, { // for older Netscapes (4-)
		string: navigator.userAgent,
		subString: "Mozilla",
		identity: "Netscape",
		versionSearch: "Mozilla"
	}],
	dataOS: [{
		string: navigator.platform,
		subString: "Win",
		identity: "Windows"
	}, {
		string: navigator.platform,
		subString: "Mac",
		identity: "Mac"
	}, {
		string: navigator.userAgent,
		subString: "iPhone",
		identity: "iPhone/iPod"
	}, {
		string: navigator.platform,
		subString: "Linux",
		identity: "Linux"
	}]
};

///// mobile
var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};

var observeDOM = (function(){
	var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
	return function( obj, callback ){
		    if( !obj || !obj.nodeType === 1 ) return; // validation

		    if( MutationObserver ){
		      // define a new observer
		      var obs = new MutationObserver(function(mutations, observer){
		          callback(mutations);
		      })
		      // have the observer observe foo for changes in children
		      obs.observe( obj, { childList:true, subtree:true });
		    }
		    
		    else if( window.addEventListener ){
		      obj.addEventListener('DOMNodeInserted', callback, false);
		      obj.addEventListener('DOMNodeRemoved', callback, false);
		    }
	}
})();

//Convert DOM objects into selector strings (tag#id.class)
function domObjectToSelector(object){
    //If a jQuery object was passed, use the proper node
    if ( !object.nodeType ){
        object = object[0];
    }

    var selector = object.nodeName.toLowerCase();

    if ( object.id ){
        selector += '#' + object.id;
    }

    if ( object.className ){
        selector += '.' + object.className.replace(/\s/g, '.');
    }

    return selector;
}

function text_fit(){
	var token_init = "text-fit-init";
	$(".text-fit").not("."+token_init).each(function(){
        fitty($(this)[0]);
        $(this).addClass(token_init);
    });	
}

function countdown_item(callback){
    $(".countdown").each(function(){
        $(this).append("<div class='countdown-text'/>");
        var seconds = $(this).data("seconds");
        var countdown = moment().add(seconds, 'seconds').format("YYYY-MM-DD HH:mm:ss");
        var text = "<span>Kalan Süre</span>%M:%S";
        $(this)
        .find(".countdown-text")
        .countdown(countdown, {elapse: false})
        .on('update.countdown', function(e) {
            var $this = $(this);
                $this.html(e.strftime(text));
        })
        .on('finish.countdown', function(e) {
        	var $this = $(this);
        	$this.html("<span class='text-danger'>Süre Doldu</span>");
        	if(!IsBlank(callback)){
        		window[callback]();
        	}
        })
        if($(this).hasClass("countdown-circle")){
            $(this).append('<svg width="130" height="130" xmlns="http://www.w3.org/2000/svg">' +
                '<g>' +
                   '<circle class="circle-bg" r="60" cy="65" cx="65" stroke-width="4" stroke="#eeeeee" fill="none"/>' +
                   '<circle class="circle" r="60" cy="65" cx="65" stroke-width="8" stroke="#c4d600" fill="none"/>' +
                '</g>' +
            '</svg>');
            var initialOffset = 390;
            var i = 1;
            var $circle =  $(this).find('.circle')
            $circle.css('stroke-dashoffset', initialOffset-(1*(initialOffset/seconds)));
            var interval = setInterval(function() {
                //$('h2').text(i);
                if (i == seconds) {    
                    clearInterval(interval);
                    return;
                }
                console.log(initialOffset-((i+1)*(initialOffset/seconds)))
                $circle.css('stroke-dashoffset', initialOffset-((i+1)*(initialOffset/seconds)));
                i++;  
            }, 1000);
        }
    });
}

function bg_check(){
	var token_init = "bg-check-init";
	var hash = [];
	$("[data-bg-check]").each(function (i, div) {
		$(this).addClass(token_init);
	    $.each($(div), function (j, obj) {
	        var attr = obj.getAttribute('data-bg-check');
	        if (!(attr in hash)) hash[attr] = [];
	        hash[attr].push(domObjectToSelector(div));
	    });
	});
	if(hash){
		for(var obj in hash){
			if(typeof hash[obj] == "object"){
				BackgroundCheck.init({
					targets: hash[obj].join(","),
					images:  obj
			    });
			    console.log({
					targets: hash[obj].join(","),
					images:  obj
			    })				
			}
		}
	}
}


    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var decodedCookie = decodeURIComponent(document.cookie);
        var ca = decodedCookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }


function getDataAttributes(node) {
    var d = {}, 
        re_dataAttr = /^data\-(.+)$/;

    $.each(node.get(0).attributes, function(index, attr) {
        if (re_dataAttr.test(attr.nodeName)) {
            var key = attr.nodeName.match(re_dataAttr)[1];
            d[key] = attr.nodeValue;
        }
    });

    return d;
}