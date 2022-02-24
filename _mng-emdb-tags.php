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

_simple()->css( <<<EOD
//- スコアのグラフ
.sbar { border: 1px solid #009; padding: 0; margin: 1px; display: inline-block;
	background: white;
	height: 0.8em; width: 2em;
	box-shadow: 0 0.1em 0.1em 0.1em rgba(0,0,0,0.2) inset;
}
.sbari { height: 100%; background: #900;
	box-shadow: 0 0.1em 0.2em 0.2em rgba(255,255,255,0.4) inset;
}

EOD
);

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
			$i_array[] = _ab( "jsonview.php?a=emdb_json.$i.$cat", $i );
		}
		$multi = [];
		foreach ( $data['ids_multi'] as $i ) {
			$multi[] = _ab( "jsonview.php?a=emdb_json.$i.$cat", $i );
		}
		foreach ( $rep as $i => $o ) 
			$tag = strtr( $tag, [ $i => $o ] );
		$tag = strtr( $tag, [
			'<~>[' => '[' ,
			'<~>' => '->'
		]);
		$rate = $data['num'] / INFO['all'];
		$num = $data['num'] == INFO['all']
			? _levelbar(1) . 'all'
			: _levelbar( $rate ) . round( $rate  * 100, 2 ). '%'
		;
		$type = $data['type'] == 'val' ? '' : $data['type'];
		if ( $multi )
			$type = _pop( $type, _long( $multi, 200 ) );
		$ret .= TR.TH. $type. TD. $tag. TD. ( $i_array
			? _pop( $num, _long( $i_array, 500 )  )
			: $num
		);

	}
	return _t( 'table', $ret );
}

//.. _levelbar
function _levelbar( $v ) {
	$v = round( $v * 100 );
	return _div( '.sbar', // . ( $wid != '' ? ' | st:width:200px' : '' ) ,
		_div( ".sbari | st:width:$v%" ) );
}


