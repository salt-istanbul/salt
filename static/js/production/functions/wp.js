function lang_predefined($text){
	if(!IsBlank($text)){
		var regex =  /<%([^}]+)%>/g;
        var matches = getAllMatches(regex, $text);
        for(var i=0;i<matches.length;i++){
        	var $word = matches[i][1].trim();
        	if(site_config.dictionary.hasOwnProperty($word)){
        	   $text = $text.replaceAll(matches[i][0], site_config.dictionary[$word]);
        	}
        }
	}
	return $text;
}

function initContactForm(){
        var wpcf7_form = document.getElementsByClassName('wpcf7-form');
        [].forEach.call(wpcf7_form, function( form ) {
            console.log(form);
          wpcf7.initForm( form );
        });
}


function contactform_sent(e){
    var formId = e.detail.contactFormId;
    if($('.modal:visible').length>0){
        modalFormActions('sent');
    }else{
        $("body").removeClass("loading-process");
        contactFormActions('sent');
    }
}

$( document ).ready(function() {
        /*wpcf7invalid — Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because there are fields with invalid input.
        wpcf7spam — Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because a possible spam activity has been detected.
        wpcf7mailsent — Fires when an Ajax form submission has completed successfully, and mail has been sent.
        wpcf7mailfailed — Fires when an Ajax form submission has completed successfully, but it has failed in sending mail.
        wpcf7submit — Fires when an Ajax form submission has completed successfully, regardless of other incidents.*/
        /*detail.contactFormId  The ID of the contact form.
        detail.pluginVersion    The version of Contact Form 7 plugin.
        detail.contactFormLocale    The locale code of the contact form.
        detail.unitTag  The unit-tag of the contact form.
        detail.containerPostId  The ID of the post that the contact form is placed in.*/
        var wpcf7Elm = document.querySelector( '.wpcf7' );
        document.addEventListener( 'wpcf7submit', function( e ) {
            //event.detail.contactFormId;
            if($('.modal:visible').length>0){
                modalFormActions('submit');
            }else{
                $("body").removeClass("loading-process");
                contactFormActions('submit');
            }
        }, false );
        document.addEventListener( 'wpcf7mailsent', function( e ) {
            //event.detail.contactFormId;
            contactform_sent(e);
        }, false );
        document.addEventListener( 'wpcf7mailfailed', function( e ) {
            $("body").removeClass("loading-process");
        }, false );
        document.addEventListener( 'wpcf7spam', function( e ) {
            $("body").removeClass("loading-process");
        }, false );
        document.addEventListener( 'wpcf7invalid', function( e ) {
            $("body").removeClass("loading-process");
        }, false );
        $(wpcf7Elm).on("submit", function(){
            if($('.modal:visible').length>0){
                $('.modal:visible').find(".modal-content").addClass("loading-process");
            }else{
                $("body").addClass("loading-process");
            }
        });
});