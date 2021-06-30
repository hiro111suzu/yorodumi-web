<?php
require( __DIR__. '/common-web.php' );
/*
?a=type.id.id2
?path=path
*/
//. get fle path
_add_fn( 'disp' );

$a = _getpost( 'a' );
if ( $a ) {
	list( $type, $id, $id2 ) = explode( '.', $a, 3 );
	$fn = $type == 'arch'
		? ( new cls_archive( $id2 ) )->path( $id )
		: _fn( $type, $id, $id2 )
	;
} else {
	$fn = _getpost( 'path' );
}

if ( ! $fn || ! file_exists( $fn ) ) {
	die( TEST ? 'no file: '. ( $fn ?: 'null' ) : 'no file found' );
}

//. content type
$ctype = [
	'xml'	=> 'text/xml',
	'json'	=> 'text/json',
	'jpg'	=> 'image/jpeg' ,
	'jpeg'	=> 'image/jpeg' ,
	'png'	=> 'image/png' ,
	'gif'	=> 'image/gif' ,
	'tif'	=> 'image/tiff' ,
	'mp4'	=> 'video/mp4' ,

	'pdf'	=> 'application/pdf' ,
][
	strtolower( pathinfo( basename( $fn, '.gz' ), PATHINFO_EXTENSION ) )
] ?: 'plain' ;

//. output
header( "Content-type: $ctype; charset=utf-8" );

if ( _is_gz( $fn ) )
	header('Content-Encoding: gzip');
readfile($fn);

