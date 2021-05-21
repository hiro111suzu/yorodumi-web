<?php
if ( $_GET[ 'prime' ] ) {
	header( "HTTP/1.1 303 See Other" ); 
	header( 'Location: prime.php?' . http_build_query( $_GET ) );
	die();
}

header( "HTTP/1.1 303 See Other" ); 
header( 'Location: quick.php?' . http_build_query( $_GET ) );
die();
