<?php
//. init
require( __DIR__. '/common-web.php' );
ini_set( "memory_limit", "512M" );

define( 'MODE'	, _getpost( 'mode' ) );
define( 'ID'	, _getpost( 'id' ) ?: _getpost( 'key' ) );

//. main 
//.. data
$ret = [];
if ( MODE == 'surflev' ) {
	$ret = _json_load2( _fn( 'emdb_json3', $_GET[ 'id' ] ) )->map->contour[0]->level;
} else if ( MODE == 'mov_data' ) {
	$ret = _mov_data();
} else if ( MODE == 'hwork_list' ) {
	foreach ( glob( DN_FDATA. '/hwork/*/*.map' ) as $pn ) {
		$ret[ strtr( basename( $pn, '.map' ), [ 'emd_' => '' ] ) ] = filesize( $pn );
	}
} else {
	$ret = [
		'error' => 'unknown mode: '. MODE
	];
}

//.. output
if ( is_array( $ret ) ) {
	header( 'content-type: application/json;' );
	die( json_encode( $ret ) );
} else {
	header( 'content-type: text/plain;' );
	die( $ret );
}

//. function
//.. mov_data
function _mov_data() {
	$ret = [
		'ok' => true
	];
	foreach ( explode( '|', _getpost( 'idlist' ) ) as $id ) {
		foreach ([
			's1' 	=> 's1.py' ,
			's2' 	=> 's2.py' ,
			's3' 	=> 's3.py' ,
			's4' 	=> 's4.py' ,
			's5' 	=> 's5.py' ,
			's6' 	=> 's6.py' ,
			'obj'	=> [ '1.obj', 'ym/1.obj' ] ,
			'mtl'	=> [ '1.mtl', 'ym/1.mtl' ] ,
			'mtx'	=> [ 'matrix.txt', 'ym/matrix.txt' ] ,
		] as $type => $fns ) {
			$time = [];
			foreach ( is_array( $fns ) ? $fns : [ $fns ] as $fn ) {
				$fn = DN_EMDB_MED. "/$id/$fn";
				if ( ! file_exists( $fn ) ) continue;
				$time[] = filemtime( $fn );
			}
			if ( count( $time ) )
				$ret[ $id ][ $type ] = max( $time );
		}
	}
	return $ret;
}

