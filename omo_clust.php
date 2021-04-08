<?php
require( __DIR__. '/common-web.php' );
require( __DIR__. '/omo-web-common.php' );
//require( __DIR__ . '/omo-calc-common.php' );

define( 'NUM_IN_PAGE', 20 );
define( 'NUM_IMG_ICON', 7 );

//. init
//define( 'SCNORM', 2.17938653227261 );

//.. ファイル
define( 'PROJ_NAME', _getpost_safe( 'proj' ) ?: 401 );
define( 'DN_PROJ', realpath( __DIR__ . '/../omo_class' ) .'/'. PROJ_NAME );

define( 'CYCLE_ORIG', _getpost_safe( 'cycle' ) ?: 0 );

define( 'CYCLE_LAST', (integer)_numonly( basename(
	array_slice( glob( _fn2( 'info' ) ), -1 )[0]
)) );

define( 'CLUST_ID', _getpost_safe( 'clust' ) );

define( 'CYCLE', CYCLE_ORIG < 1
	? CYCLE_LAST + CYCLE_ORIG
	: (integer)CYCLE_ORIG
);
$json = _json_load2( _fn2( 'info', CYCLE ) );

//. ajax reply
if ( _getpost_safe( 'ajax' ) == 'list' ) {
	die( _datalist() );
}
if ( _getpost_safe( 'ajax' ) == 'str_list' ) {
	die( _str_list( CLUST_ID ) );
}

//. contents
//.. form
$_simple->hdiv( 'Query' ,
	_t( 'form| method:get', _simple_table([
		'Project' =>
			_t( "input| type:text| name:proj| size:10| value:" .PROJ_NAME ) ,

		'Cycle #' =>
			_t( "input| type:text| name:cycle| size:10| value:" .CYCLE_ORIG ) ,
		'Sort by' =>
			_radiobtns([
				'name' => 'sortby' ,
				'on' => _getpost_safe( 'sortby' ) ?: 'id' ,
			],[
				'id'         => 'Cluster ID' ,
				'size'       => 'Size (small-)' ,
				'size-'      => 'Size (large-)' ,
				'score_avg'  => 'Averaged score (low-)' ,
				'score_avg-' => 'Averaged score (high-)' ,
				'score_min'  => 'Lowest score (low-)' ,
				'num'        => 'number of member (low-)' ,
			])
	])
	. _e( 'input| .submitbtn| type:submit' )
	)
);

//.. summary
if ( $json ) {
	$_simple->hdiv( 'Summary', ''
		. _simple_table([
			'Score'     		=> _format_stat( $json->summary->score ) ,
			'Number of cluster member' => _format_stat( $json->summary->count ) ,
			'Number of cycles'	=> CYCLE_LAST ,
			'Converged'			=> _json_load2( _fn2( 'step', CYCLE_LAST ) )->min == 1
				? 'Yes' : 'Not yet' 
		])
	);
}

//.. data
CLUST_ID == ''
	? $_simple->hdiv( 'Clusters', _datalist(), [ 'id' => 'list' ] )
	: $_simple->hdiv( "Cluster #$c", _data_item( CLUST_ID, true ) )
;

//.. json
/*
$_simple->hdiv( 'JSON', ''
	. _t( 'pre', _json_pretty( $json ) ),
	[ 'hide' => true ]
);
*/

//. about
$_simple->about = _ej([
	'Omokage clustering.'
],[
	'Omokageクラスタリング' 
]);

//. output
$_simple

//.. css
->css(<<<EOD
.sbar {
	border: 1px solid #aaa; padding: 0px; margin: 0; display: inline-block;
	height: 0.7em; width: 10em;
}
.sbari { height: 100%; background: $col_dark; }
EOD

//.. output
)->out([
	'title' => _ej( 'Omokage clustering', 'Omokageクラスタ' ) ,
	'sub'	=> _ej( 'Shape clustering by Omokage', 'Omokageによる概形クラスタ' ) ,
	'icon'	=> 'omokage' ,
	'js'	=> $js ,
]);


//. function
//.. _datalist
function _datalist() {
	global $json, $_simple;
	if ( ! $json ) return "no json info";
	
	//... sort
	$sort = [];
	$sortby = _getpost_safe( 'sortby' );
	$sortrev = _instr( '-', $sortby );
	$sortby = trim( $sortby, '-' );
	foreach ( $json as $clust_id => $j ) {
		if ( $clust_id == 'summary' ) continue;
		$v = $clust_id;
		if      ( $sortby == 'score_avg' ) $v = $json->$clust_id->stat->avg;
		else if ( $sortby == 'score_min' ) $v = $json->$clust_id->stat->min;
		else if ( $sortby == 'num'       ) $v = $json->$clust_id->stat->num;
		else if ( $sortby == 'size'      ) $v =
			$json->$clust_id->pca[0] *
			$json->$clust_id->pca[1] *
			$json->$clust_id->pca[2]
		;
		$sort[ $clust_id ] = $v;
	}
	if ( $sortrev )
		arsort( $sort );
	else
		asort( $sort );
	$sort = array_keys( $sort );

	//... main loop
	$page = _getpost_safe( 'page' ) ?: 0 ;
	$start = $page * NUM_IN_PAGE;
	$list = '';
	foreach ( range( $start, $start + NUM_IN_PAGE ) as $num ) {
		$clust_id = $sort[ $num ];
		if ( $clust_id == '' ) continue;
		$list .= $_simple->hdiv(
			"Cluster #$clust_id" , 
			_data_item( $clust_id ) ,
			[ 'type' => 'h2' ]
		);
	}

	//... ページャ
	$opg = new cls_pager([
//		'str'	=> 'omo cluster ' ,
		'total'	=> count( (array)$json ) - 1 ,
		'page'	=> $page ,
		'range'	=> NUM_IN_PAGE ,
		'div'	=> '#oc_div_list' ,
		'pvar'	=> $_GET + [ 'ajax' => 'list' ]
	]);
	return $opg .$opg->btn() . $list . $opg->btn();
}

//.. _data_item
function _data_item( $clust_id, $flg_all_str = false ) {
	global $json;
	$j = $json->$clust_id;
	if ( ! $j ) return;
	$imgs = '';
	//... images
	if ( $flg_all_str ) {
		$imgs = _str_list( $clust_id );
	} else foreach ( range( 1, NUM_IMG_ICON ) as $rank ) {
		if ( ! $j->$rank ) continue;
		$oid = new cls_omoid( $j->$rank->id );
		$s = $j->$rank->sc;
		$imgs .= _pop(
			$oid->imgfile,
			_kv([ 'Rank' => $rank, 'Score' => _levelbar( $s ) . " $s" ])
			.BR. $oid->desc2(),
			[
				'type' => 'img',
				'trgopt' => ".iimg enticon"
			]
		);
	}

	//... まとめ
	return _simple_table([
		'Score' => _levelbar( $j->stat->avg ) . _format_stat( $j->stat ) ,
		'PCA (&Aring;)' => _imp([
			_v( $j->pca[0], 1 ) ,
			_v( $j->pca[1], 1 ) ,
			_v( $j->pca[2], 1 ) ,
		]) ,
		'Number of structures' => $j->stat->num
			. ( $flg_all_str ? '' 
				: ' '. _ab( '?proj=' .PROJ_NAME. "&clust=$clust_id", 'show all' )
			) ,
		'Similar structures' => _p( $imgs )
	]);
}

//.. _str_list
function _str_list( $clust_id ) {
	//... read data
	$data = [];
	foreach ( _file( _fn2( 'cls', CYCLE, $clust_id ) ) as $line ) {
		list( $id, $sc ) = explode( "\t", $line );
		$data[ trim( $id ) ] = trim( $sc );
	}
	arsort( $data );

	$page = _getpost_safe( 'page' ) ?: 0 ;
	$start = $page * NUM_IN_PAGE;
	$ids = array_keys( $data );
	$scores = array_values( $data );

	//... list items
	$item = '';
	foreach ( range( $start, $start + NUM_IN_PAGE ) as $num ) {
		if ( ! $ids[ $num ] ) continue;
		$oid = new cls_omoid( $ids[ $num ] );
		$items .= _div( '.topline clearfix', ''
			. _img( '.left', $oid->imgfile )
			. '<b>#' . ( $num + 1 ) . '</b>'
			. _levelbar( $scores[ $num ] )
			. ' Score: ' . $scores[ $num ]
			. BR
			. $oid->desc2()
		);
	}
	
	//... pager
	$opg = new cls_pager([
		'total'	=> count( $data ),
		'page'	=> $page ,
		'range'	=> NUM_IN_PAGE ,
		'div'	=> '#str_list' ,
		'pvar'	=> $_GET + [ 'ajax' => 'str_list' ]
	]);
	return _div( '#str_list', $opg . $items . $opg->btn() );
}


//.. _format_stat
function _format_stat( $obj ) {
	return '<b>'
		. _v( $obj->avg ) .'</b> +/- '
		. _v( $obj->sigma ) .', '
		. _v( $obj->min ) .' ~ '
		. _v( $obj->max )
	;
}

function _v( $val, $o = 3 ) {
	return number_format( $val, $o );
}

//.. _levelbar
function _levelbar( $v ) {
	$p = round( $v * 100 );
	return _div( '.sbar', // . ( $wid != '' ? ' | st:width:200px' : '' ) ,
		_div( ".sbari | st:width:$p%" ) );
}

//.. _keta_format
function _keta_format( $num = '*', $keta = 3 ) {
	return $num === '*' ? '*' : substr( '0000000' . $num, $keta * -1 );
}

//.. _fn2
function _fn2( $type, $cycle = '*', $val1 = '*' ) {
	$ret = [
		'dn_cls'    => 'cls<num>' ,
		'cls'       => 'cls<num>/' . _keta_format( $val1 ) . '.tsv' ,
		'profdb'    => 'prof<num>.sqlite' ,
		'step'		=> 'step<num>.json' ,
		'info'		=> 'info<num>.json' ,
		'nodes_pdb' => "nodes_$cycle.pdb"
	][ $type ];
	if ( $ret == '' )
		_die( 'ファイル名タイプが不明: ' . $type );
	return DN_PROJ . '/' . strtr( $ret, [ '<num>' => _keta_format( $cycle )] );
}
