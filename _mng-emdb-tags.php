<?php
//. init
require( __DIR__ . '/_mng.php' );

$json = _json_load( DN_PREP. '/emdb_all_tags.json' );
define( 'INFO', $json['_info'] );
unset( $json['_info'] );

//. カテゴリ分け
$categs = [];
foreach ( $json as $k => $v ) {
	$categs[ explode( '<~>', $k, 2 )[0] ][ $k ] = $v;
}

//. main
foreach ( $categs as $cat => $val ) {
	_simple()->hdiv( "categ: $cat", _table( $val ) );
}

//. func: table
function _table( $val ) {
	global $cat;
	$ret = '';

	//- multi/single
	$rep = [];
	foreach ( $val as $k => $v ) {
		if ( $v['type' ] == 'single' )
			$rep[ "$k<~>[~]" ] = "$k<~>[0]";
		if ( $v['type' ] == 'multi' )
			$rep[ "$k<~>[~]" ] = "$k<~>[*]";
	}
	$rep = array_reverse( $rep );
	foreach ( $val as $tag => $data ) {
		$i_array = [];
		foreach ( $data['ids'] as $i ) {
			$i_array[] = _ab( "jsonview.php?a=emdb_json3.$i.$cat", $i );
		}
		$multi = [];
		foreach ( $data['ids_multi'] as $i ) {
			$multi[] = _ab( "jsonview.php?a=emdb_json3.$i.$cat", $i );
		}
		foreach ( $rep as $i => $o ) 
			$tag = strtr( $tag, [ $i => $o ] );
		$tag = strtr( $tag, [
			'<~>[' => '[' ,
			'<~>' => '->'
		]);
		$num = $data['num'] == INFO['all']
			? 'all'
			: round( $data['num'] / INFO['all']  * 100, 2 ). '%'
		;
		$type = $data['type'] == 'val' ? '' : $data['type'];
		if ( $multi )
			$type = _pop( $type, _ul( $multi ) );
		$ret .= TR.TH. $type. TD. $tag. TD. ( $i_array
			? _pop( $num, _ul( $i_array )  )
			: $num
		);

	}
	return _t( 'table', $ret );
}

