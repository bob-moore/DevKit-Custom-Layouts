jQuery( function( $ ) {
	'use strict';
	$('a.devkit-layout-edit').on( 'click', ( event ) => {
		window.open(event.currentTarget.href);
	} );
});