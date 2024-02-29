var jq, profileHeader;
if ( typeof jq === "undefined" ) {
	jq = jQuery;
}

jq( function() {
	var memberLoop, groupMemberLoop,
		buttonSelector = 'a';

	if ( jq( 'body.bp-nouveau' ).length ) {
		profileHeader   = jq("ul.member-header-actions"),
		memberLoop      = jq("#members-dir-list").parent();
		groupMemberLoop = jq("#members-group-list").parent();
	} else {
		profileHeader   = jq("#item-buttons"),
		memberLoop      = jq("#members-list").parent(),
		groupMemberLoop = jq("#member-list").parent();
	}

	profileHeader.on("click", ".resell-button " + buttonSelector, function() {
		bp_resell_button_action( jq(this), 'profile' );
		return false;
	});

	memberLoop.on("click", ".resell-button " + buttonSelector, function() {
		bp_resell_button_action( jq(this), 'member-loop' );
		return false;
	});

	groupMemberLoop.on("click", ".resell-button " + buttonSelector, function() {
		bp_resell_button_action( jq(this) );
		return false;
	});
} );

function bp_resell_button_action( scope, context ) {
	var link = scope,
		uid = link.attr('id'),
		nonce  = link.attr('href'),
		action = '';

	uid    = uid.split('-');
	action = uid[0];
	uid    = uid[1];

	nonce = nonce.split('?_wpnonce=');
	nonce = nonce[1].split('&');
	nonce = nonce[0];

	link.addClass( 'loading' );

	link.trigger( 'bpResell:beforeAjax', {
		action: action,
		context: context
	} );

	jq.post( ajaxurl, {
		action: 'bp_' + action,
		'uid': uid,
		'link_class': link.attr( 'class' ).replace( 'loading', '' ),
		'_wpnonce': nonce
	},
	function(response) {
		jq( link.parent()).fadeOut(200, function() {
			// toggle classes
			if ( action === 'stop_resell' ) {
				link.parent().removeClass( 'reselling' ).addClass( 'not-reselling' );
			} else {
				link.parent().removeClass( 'not-reselling' ).addClass( 'reselling' );
			}

			// add ajax response
			link.parent().html( response.data.button );

			// increase / decrease counts
			var count_wrapper = false;
			if ( context === 'profile' ) {
				count_wrapper = jq("#user-members-resellers span");

			} else if ( context == 'member-loop' ) {
				// this means we're on the member directory
				if ( jq(".dir-search").length ) {
					count_wrapper = jq("#members-reselling span");

				// a user is on their own profile
				} else if ( ! jq.trim( profileHeader.text() ) ) {
					count_wrapper = jq("#user-members-reselling span");
				}
			}

			if ( count_wrapper.length ) {
				if ( action == 'stop_resell' ) {
					count_wrapper.text( ( count_wrapper.text() >> 0 ) - 1 );
				} else if ( action === 'resell' ) {
					count_wrapper.text( ( count_wrapper.text() >> 0 ) + 1 );
				}
			}

			jq(this).fadeIn(200);

			jq(this).trigger( 'bpResell:complete', {
				action: action,
				context: context,
				response: response.data
			} );
		});
	});
}
