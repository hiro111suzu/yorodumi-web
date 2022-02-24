<?php
//. init
ini_set( "memory_limit", "512M" );
define( 'IMG_MODE', 'test' );
require( __DIR__. '/common-web.php' );




//. output
_simple()->hdiv(
	'input' ,
	_t( 'form| method:post | enctype:multipart/form-data ', ''
		. _t( 'input| type:file| required| name:file' )
		. _t( 'input| type: submit' )
	)
)
->hdiv(
	'$_FILES' ,
	_t( 'pre', print_r( $_FILES, true ) )
)
->hdiv(
	'$_REQUEST' ,
	_t( 'pre', print_r( $_REQUEST, true ) )
)
->out();

