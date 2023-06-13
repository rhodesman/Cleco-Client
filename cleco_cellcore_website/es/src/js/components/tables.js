$(document).ready(function() {

	var tableSelector = "body";

	$( tableSelector ).find( "table" ).wrap( '<div class="responsive-table"></div>' ).wrap( '<div class="responsive-table-inner"></div>' );

	$( window ).on( "load resize", function() {
		$( tableSelector ).find( ".responsive-table" ).each( function() {
			var $inner = $( this ).find( ".responsive-table-inner" );
			$( this ).toggleClass( "is-scrollable", ( $inner.get( 0 ).scrollWidth > $inner.outerWidth() + 10 ) );
		});
	});	


});