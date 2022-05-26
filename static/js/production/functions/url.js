function IsUrl(s) {
   var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
   return regexp.test(s);
}

function getUrlVars(url) {
    var hash;
    var myJson = {};
    var hashes = url.slice(url.indexOf('?') + 1).split('&');
    console.log(hashes)
    if(hashes.length>0 && url.indexOf('?')>-1){
	    for (var i = 0; i < hashes.length; i++) {
	        hash = hashes[i].split('=');
	        if(typeof hash[1] !== "undefined"){
	        	myJson[hash[0]] = hash[1];
	        }
	    }    	
    }
    return myJson;
}

function url2json(str) {
  /*var reg = /[^?]*\??([^&]+)=([^&]+)/g, result, obj = {};
  while(result = reg.exec(str)) {
    obj[result[1]] = result[2];
  }
  return obj;*/
  return getUrlVars(str);
}

function json2url(json) {
  var arr= [];
  for (var k in json) {
    if (json.hasOwnProperty(k)) {
      arr.push(k + '=' + json[k]);
    }
  }
  return arr.join('&');
}


//Get querystring value
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
    results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function removeQueryString(key) {
  var urlValue=document.location.href;
  
  //Get query string value
  var searchUrl=location.search;
  
  if(key!="") {
    oldValue = getParameterByName(key);
    removeVal=key+"="+oldValue;
    if(searchUrl.indexOf('?'+removeVal+'&')!= "-1") {
      urlValue=urlValue.replace('?'+removeVal+'&','?');
    }
    else if(searchUrl.indexOf('&'+removeVal+'&')!= "-1") {
      urlValue=urlValue.replace('&'+removeVal+'&','&');
    }
    else if(searchUrl.indexOf('?'+removeVal)!= "-1") {
      urlValue=urlValue.replace('?'+removeVal,'');
    }
    else if(searchUrl.indexOf('&'+removeVal)!= "-1") {
      urlValue=urlValue.replace('&'+removeVal,'');
    }
  }
  else {
    var searchUrl=location.search;
    urlValue=urlValue.replace(searchUrl,'');
  }
  history.pushState({state:1, rand: Math.random()}, '', urlValue);
}