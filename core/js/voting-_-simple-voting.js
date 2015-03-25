var SV_VOTING_DIV_NAME_PREFIX = "svVotingDiv";		// Global "constant".
var svFormToSubmitAfterSuccessfulAjaxCall = null;	// Global variable.

function isValidEmail(email) { 
	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}
function getCookie(name) {
	var matches = document.cookie.match( new RegExp(
		"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}
function setCookie(name, value, options) {
	options = options || {};

	var expires = options.expires;

	if (typeof expires == "number" && expires) {
		var d = new Date();
		d.setTime(d.getTime() + expires*1000);
		expires = options.expires = d;
	}
	if (expires && expires.toUTCString) {
		options.expires = expires.toUTCString();
	}

	value = encodeURIComponent(value);

	var updatedCookie = name + "=" + value;

	for(var propName in options) {
		updatedCookie += "; " + propName;
		var propValue = options[propName];   
		if (propValue !== true) {
			updatedCookie += "=" + propValue;
		}
	}

	document.cookie = updatedCookie;
}


function show_voting_results( response ) {
	// This function called if ajax request was successfull.
	
	// If ajax request was made from the form with a NOT EMPTY "action" attribute,
	// we must submit this form right now.
	if( null != svFormToSubmitAfterSuccessfulAjaxCall ) {
		svFormToSubmitAfterSuccessfulAjaxCall.submit();
	} else {
	
		// Ok, we must change the appearance of all the voting DIVs with same txtID, as in response.
		var txtID = jQuery("sv_subjectId", response).text().trim();
		
		var xmlDoc = jQuery.parseXML( response ),
			xml = jQuery( xmlDoc ),
			statInfo = xml.find('sv_htmlStatistics');
			
		// Voting...
		jQuery( "[name=" + SV_VOTING_DIV_NAME_PREFIX + txtID + "]" ).each(function( index ) {
			jQuery( this ).html( statInfo.text() );
		});
	}
}

function vote_error( textError ) {
	if (typeof textError == 'string')
	if( textError.length > 0 ) alert( textError );
}

function voteClicked( event, voteValue ) {                          
	event.preventDefault();
	
	// Let's find parent voting DIV, and (if all input fields filled properly) send ajax request.
	jQuery(event.target).closest( "[name^=" + SV_VOTING_DIV_NAME_PREFIX + "]" ).each(function( event, index ) {
		
		var votingDiv = jQuery( this );
		var txtID = votingDiv.attr("name").substr( SV_VOTING_DIV_NAME_PREFIX.length );


	
		votingDiv.find("[name=svVotingGroup]" ).css("visibility","hidden");
		
		var canSubmit = true;
		
		var userNameField = votingDiv.find( "[name=svUserName]" ).first();
		if( 0 == userNameField.val().length ) {
			canSubmit = false;
			userNameField.toggleClass( "text-label-alarm-_-simple-voting", true );
		} else {
			userNameField.toggleClass( "text-label-alarm-_-simple-voting", false );
		}
		
		var userEmailField = votingDiv.find( "[name=svUserEmail]" ).first();
		if( !isValidEmail( userEmailField.val() ) ) {
			canSubmit = false;
			userEmailField.toggleClass( "text-label-alarm-_-simple-voting", true );
		} else {
			userEmailField.toggleClass( "text-label-alarm-_-simple-voting", false );
		}
		
		if( !canSubmit ) {
			votingDiv.find("[name=svVotingGroup]" ).css("visibility","visible");
		} else {
			votingDiv.find("[name=sv_vote]" ).val( voteValue );
			
			setCookie( "svUserName"+txtID, votingDiv.find("[name=svUserName]" ).val(), { expires: 1000000 } );
			setCookie( "svUserEmail"+txtID, votingDiv.find("[name=svUserEmail]" ).val(), { expires: 1000000 } );
			setCookie( "svUserComment"+txtID, votingDiv.find("[name=svUserComment]" ).val(), { expires: 1000000 } );
			
			var svForm = votingDiv.find("form[name=svVotingForm]" );
			
			// If svVotingForm's address is NOT empty, we should submit this form after
			// returning from the ajax call (see show_voting_results function).
			if( svForm.attr( "action" ).length > 0 ) {
				svFormToSubmitAfterSuccessfulAjaxCall = svForm;
			}
			
			wp.ajax.send( "simple_voting",
				{
					success: show_voting_results,
					error:   vote_error,
					data: svForm.serialize()
				}
			);
		}
	});
};

function processAllVotingDivs() {
	jQuery( "[name^=" + SV_VOTING_DIV_NAME_PREFIX + "]" ).each(function( index ) {
		
		var votingDiv = jQuery( this );
		
		// We will process only divs, that contain the voting form.
		if( 0 < votingDiv.find("form[name=svVotingForm]" ).length ) {
			
			var txtID = votingDiv.attr("name").substr( SV_VOTING_DIV_NAME_PREFIX.length );
			
			var userNameCookie = getCookie( "svUserName"+txtID );
			var userEmailCookie = getCookie( "svUserEmail"+txtID );
			var userCommentCookie = getCookie( "svUserComment"+txtID );

			if( typeof(userNameCookie ) != "undefined" ) {
				votingDiv.find( "[name=svUserName]" ).val( userNameCookie );
				votingDiv.find( "[name=svUserNameGroup]" ).hide();
			}
			if( typeof(userEmailCookie ) != "undefined" ) {
				votingDiv.find( "[name=svUserEmail]" ).val( userEmailCookie );
				votingDiv.find( "[name=svUserEmailGroup]" ).hide();
			}
			if( typeof(userCommentCookie ) != "undefined" ) {
				votingDiv.find( "[name=svUserComment]" ).val( userCommentCookie );
			}
		}
	});
};

jQuery(document).ready(function() {
	processAllVotingDivs();
});
