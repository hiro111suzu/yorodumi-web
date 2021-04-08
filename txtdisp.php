<?php
require( __DIR__. '/common-web.php' );
_add_fn( 'txtdisp' );

list( $type, $id ) = explode( '.', _getpost( 'a' ), 2 );
$type	= $type ?: _getpost( 'type' );
$id		= $id   ?: _getpost( 'id' );
$fn = _fn( $type, $id ) ?: _url( $type, $id ) ?: _getpost( 'path' );
$ext = '';

if ( $fn == '' || ! file_exists( $fn ) ) {

	if ( file_exists( $path ) ) {
		$fn = $path;
	} else {
		die( 'no file: '. ( $fn ?: 'null' ) );
	}
}
$ext = strtolower( pathinfo( basename( $fn, '.gz' ), PATHINFO_EXTENSION ) );

$type = [
	'xml'	=> 'text/xml',
	'json'	=> 'text/json',
	'jpg'	=> 'image/jpeg' ,
	'jpeg'	=> 'image/jpeg' ,
	'png'	=> 'image/png' ,
	'gif'	=> 'image/gif' ,
	'tif'	=> 'image/tiff' ,
	'mp4'	=> 'video/mp4' ,

	'pdf'	=> 'application/pdf' ,
][ $ext ] ?: 'plain' ;

header( "Content-type: $type; charset=utf-8" );

if ( _is_gz( $fn ) )
	header('Content-Encoding: gzip');
readfile($fn);

