<?php
require( __DIR__ . '/lgc-emn-common.php' );

$id = _getpost_safe( 'id' );
$fn = _fn( 'mmcif', strtolower( _getpost_safe( 'id' ) ) ?: '100d' );
if ( ! file_exists( $fn ) ) {
	$fn = DN_FDATA . "/large_structures_asb/$id-assembly1.cif.gz";
	if ( ! file_exists( $fn ) ) {
		die( "no cif file for $id: $n ($fn)" );
	}
}
echo _t( 'pre',
	implode( '', _cif2pdb( gzfile( $fn ) ) )
);

