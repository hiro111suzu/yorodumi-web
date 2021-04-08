<?php
require( __DIR__. '/common-web.php' );

ini_set( "memory_limit", "2048M" );
define( 'MAXLINES', 5000 );

//. get解釈
define( 'FN_TSV', _getpost( 'a' ) ?: _getpost( 'path' ) );

$data = [];
$header = 'table';
foreach ( (array)_file( FN_TSV ) as $line) {
	$line = explode( "\t", $line );
	if ( $line[0] == '.' ) {
		if ( $data ) {
			_add_table( $header, $data );
			$data = [];
		}
		$header = $line[1];
		continue;
	}
	$data[] = TH. implode( TD, $line );
}
if ( $data )
	_add_table( $header, $data );

//. output
$_simple->out([
	'title' => 'TSV view' ,
	'sub'	=> 'TSV file viewer' ,
	'icon'	=> 'json' ,
]);

//. function
//.. _data2table
function _add_table( $header, $data ) {
	global $_simple;
	$_simple->hdiv(
		$header,
		_t( 'table', TR. implode( TR, $data ) ) 
//		[ 'type' => 'h2' ]
	);
}


