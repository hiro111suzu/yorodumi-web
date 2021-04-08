<?php 
//. init
define( 'COLOR_MODE', 'emn' );
require( __DIR__. '/common-web.php' );

define( 'ARC_EMDB', '/home/archive/ftp/pdbj-pre/pub/emdb' );
$json = _json_load2( ARC_EMDB. '/status/latest/emdb_update.json' );
$date = $json->releaseDate;

_add_fn([
	'dir' => ARC_EMDB. '/structures/EMD-<id>' ,
	'map' => ARC_EMDB. '/structures/EMD-<id>/map/emd_<id>.map.gz' ,
	'xml' => ARC_EMDB. '/structures/EMD-<id>/header/emd-<id>.xml' ,
]);

//. main
$no_dir = $no_map = $no_xml = $ok = [];
foreach ( $json->mapReleases->entries as $id ) {
	$id = explode( '-', $id, 2 )[1];
	
	if ( ! is_dir( _fn( 'dir', $id ) ) ) {
		$no_dir[] = $id;
		$continue;
	}
	$is_ok = true;
	if ( ! file_exists( _fn( 'map', $id ) ) ) {
		$no_map[] = $id;
		$is_ok = false;
	}
	if ( ! file_exists( _fn( 'xml', $id ) ) ) {
		$no_xml[] = $id;
		$is_ok = false;
	}
	if ( $is_ok )
		$ok[] = $id;
}

//. output

$_simple->page_conf([
	'title' 	=> 'ArchCheck', 
	'icon'		=> 'emn' ,
	'openabout'	=> false ,
])
->hdiv(
	'New rel',
	_table_2col([
		'rel date' => $json->releaseDate ,
		'map rel'  => $json->mapReleases->total ,
	])
)
->hdiv(
	'No directory' . _num( $no_dir ),
	_imp( $no_dir ?: 'none' )
)
->hdiv(
	'No map'. _num( $no_map ) ,
	_imp( $no_map ?: 'none' )
)
->hdiv(
	'No header'. _num( $no_xml ) ,
	_imp( $no_xml ?: 'none' )
)
->hdiv(
	'OK'. _num( $ok ) ,
	_imp( $ok ?: 'none' )
)

//.. end
->out();

//. func
function _num( $n ) {
	return  _kakko( count( $n ) ?: '0' );
}

