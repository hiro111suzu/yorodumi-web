<?php
require( __DIR__. '/common-web.php' );

//. get fle path
_add_fn( 'disp' );

list( $type, $id ) = explode( '.', _getpost( 'a' ), 2 );
$type	= $type ?: _getpost( 'type' );
$id		= $id   ?: _getpost( 'id' );

//- テストサーバーと本番サーバーでパスが違う
if ( substr( $type, -1 ) == '_' )
	$type .= TESTSV ? 'fs3' : 'mainsv';

$fn = _fn( $type, $id ) ?: _url( $type, $id ) ?: _getpost( 'path' );
if ( $fn == '' || ! file_exists( $fn ) ) {
	die( 'no file: '. ( $fn ?: 'null' ) );
}

//. type
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

//. output
header( "Content-type: $type; charset=utf-8" );

if ( _is_gz( $fn ) )
	header('Content-Encoding: gzip');
readfile($fn);

