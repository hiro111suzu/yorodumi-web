<?php 
/*
VaProSから利用、消さない!!
*/
require( __DIR__. '/common-web.php' );

$inchi = '';
$inchikey = '';


foreach ( _json_load2([
	'chem_json',
	strtoupper( _getpost_safe( 'id' ) )
])->pdbx_descriptor as $c ) {
	if ( $c->type == 'InChIKey' )
		$inchikey = $c->descriptor;
	if ( $c->type == 'InChI' )
		$inchi = $c->descriptor;
}
header( 'content-type: application/json;' );
die( json_encode( compact( 'inchi', 'inchikey' ) ) );
