<?php

//. init
define( 'IMG_MODE', 'em' );
define( 'COLOR_MODE', 'omo' );
ini_set( 'memory_limit', '1024M' );
require( __DIR__. '/common-web.php' );

//- cache
define( 'DN_CACHE', DN_DATA. '/fh_search_cache' );

_add_lang( 'fh-search' );
//_add_fn(   'esearch' );
//_define_term( <<<EOD
//EOD
//);
define( 'G_ID'		, _getpost( 'id' ) ?: 'e0001' );
define( 'G_PAGE'	, _getpost( 'page' ) ?: 0 );
define( 'G_AJAX'	, _getpost( 'ajax' ) );
define( 'G_MODE'	, _getpost( 'mode' ) ?: 'icon' );
define( 'G_TYPE'	, _getpost( 'type' ) ?: 'all' );
define( 'ID2'		, _getpost( 'id2' ) );

define( 'G_RANGE'	, G_MODE == 'icon' ? 100 : 50 );
define( 'SCORE_COLUMN', [
	'f'		=> 'score_f' ,
	'h'		=> 'score_h' ,
	'c'		=> 'score_c' ,
	'hc'	=> 'score_h + score_c' ,
][ G_TYPE ] ?: 'score' );

$o_id = new cls_entid( G_ID );
extract( $o_id->get() ); //- $db, $DB, $id, $did
define( 'ID', $db == 'emdb' ? "e$id" : $id );

/*
$o_fh_calc = new cls_fh_calc([
	'id'   => ID ,
	'id2'  => _getpost('id2') ,
	'type' => _getpost('type') ,
]);
*/
//. term
_define_term( <<<EOD
TERM_NO_ITEM
	No item found
	見つかりませんでした
EOD
);

//. ajax reply
if ( G_AJAX == 'list' )
	die( _get_result() );
if ( G_AJAX == 'small' )
	die( _small_search() );
if ( G_AJAX == 'similarity' )
	die( _similarity() );

//. ページ作成
$_simple
->page_conf([
	'title' 	=> _ej( 'F&H Search', 'F&H 検索' ),
	'icon'		=> 'YM' ,
	'openabout'	=> false ,
//	'js'		=> [  ] ,
	'docid' 	=> 'about_fh_search' ,
	'newstag'	=> 'omo' ,
//	'auth_autocomp' => true ,
])

//.. formエリア & resultエリア
->hdiv( 'Query' ,
	_t( 'form | autocomplete:off | #form1', _table_2col([
		'ID'	=> _idinput( G_ID, [ 'acomp' => 'kw' ] ) ,
		'Type'	=> _radiobtns( [ 'name' => 'type', '#item_type', 'on' => G_TYPE ], [
			'all'	=> 'All' ,
			'f'		=> 'Function' ,
			'h'		=> 'Homology' ,
			'c'		=> 'Component' ,
			'hc'	=> 'Homology & Component' ,
		]) ,
		'Display mode' => ID2
			? '' 
			: _radiobtns( [ 'name' => 'mode', '#disp_mode', 'on' => G_MODE ], [
				'icon'	=> 'icons' ,
				'list'	=> 'list' ,
			])
	])
	. _input( 'hidden', 'name:id2', ID2 )
	. _input( 'submit', 'st: width:20em' )
	)
	. _p( $o_id->ent_item_list() )
	. ( ID2 ? ( new cls_entid( ID2 ) )->ent_item_list() : '' )
//	. _p( '$id: '. ID )
)

//.. result エリア
->hdiv( ID2 ? 'Comparison' : 'Search result',
	ID2 ? _comparison() : _get_result() ,
	[ 'id' => 'result' ]
)

//.. css
->css( <<<EOD
//- スコアのグラフ
.sbar { border: 1px solid #009; padding: 1px; margin: 1px; display: inline-block;
	background: white;
	height: 0.5em; width: 10em;
	box-shadow: 0 0.1em 0.1em 0.1em rgba(0,0,0,0.2) inset;
}
.sbari { height: 100%; background: #009;
	box-shadow: 0 0.1em 0.2em 0.2em rgba(255,255,255,0.4) inset;
}

EOD
)

//.. js
->jsvar([ 'postdata' =>[
	'id' => ID ,
	'mode' => G_MODE ,
	'type' => G_TYPE
]])
->js( <<<EOD
function _page( num ) {
	$('#oc_div_result')._loadex({
		u:'?ajax=list&page=' + num ,
		v: phpvar.postdata ,
	});
}
EOD
)

//.. output
->out();

//. function
//.. get _get_result
function _get_result() {
	$data = _search();
	//- output
	$o_pager = new cls_pager([
		'total'		=> count( $data ) ,
		'page'		=> G_PAGE ,
		'range'		=> G_RANGE ,
		'func_name' => '_page' ,
		'objname'	=> 'catalog' ,
	]);
	return $data ? ''
		. $o_pager
		. _catalog(
			array_slice( $data, G_PAGE * G_RANGE, G_RANGE ) ,
			[ 'mode' => G_MODE ] 
		)
		. $o_pager->btn()
	: TERM_NO_ITEM;
}

//.. _small_search
function _small_search() { 
	$data = array_slice( _search(), 0, 10 );
	return $data ? _catalog( $data ) : TERM_NO_ITEM;
}

//.. _catalog
function _catalog( $data ) {
	$ret = '';
	foreach ( (array)$data as $id => $score ) {
		$ret .= ( new cls_entid() )->set( $id )->ent_item( G_MODE, [
			'add_txt' => _levelbar( $score ). ' '
				. _ab([
					'fh-search' ,
					'id' => ID ,
					'id2' => $id ,
					'type' => G_TYPE ,
				], _l('Score'). ': '. round( $score, 4 ) )
/*
			. _pop_ajax( _l('Score'). ': '. round( $score, 4 ), [
				'fh-search' ,
				'ajax' => 'similarity' ,
				'ids'  => ID. '|'. $id ,
				'type' => G_TYPE ,
			])
			. ( TEST ? SEP. _ab([
				'fh-search' ,
				'id' => ID ,
				'id2' => $id ,
				'type' => G_TYPE ,
			], 'comparison' ) : '' )
*/
		]);
	}
	return $ret;
}

//.. _search
function _search() {
	//... キャッシュ
	$fn_cache = DN_CACHE. '/'. ID. '-'. G_TYPE. '.json.gz';
	if ( file_exists( $fn_cache ) ) {
		if ( filemtime( DN_DATA. '/dbid2strids.sqlite' ) < filemtime( $fn_cache ) ) {
			_testinfo( 'cache' );
			return _json_load( $fn_cache );
		}
	}
	
	//... クエリ情報
	_testinfo( 'on demand' );
	list( $key_items, $key_score ) = _get_item_score( ID );

	//... 検索
	$data = [];
	foreach ( $key_items as $fh_id ) {
		$strids = $score = null;
		extract( _ezsqlite([
			'dbname' => 'dbid2strids' ,
			'select' => [ 'strids', 'score' ] ,
			'where'  => [ 'dbid', $fh_id ]
		]) );
		if ( $score < 0.0001 ) continue;
		foreach ( explode( '|', $strids ) as $i ) {
			if ( !$i || $i == ID ) continue;
			$data[ $i ] += $score;
		}
	}

	//... ノーマライズ
	foreach ( array_keys( $data ) as $str_id ) {
		$data[ $str_id ] /= (( _ezsqlite([
			'dbname' => 'strid2dbids' ,
			'select' => SCORE_COLUMN ,
			'where'  => [ 'strid', $str_id ]
		]) + $key_score ) / 2 );
		if ( $data[ $str_id ] < 0.0001 )
			unset( $data[ $str_id ] );
	}
	arsort( $data );
	_json_save( $fn_cache, $data );
	return $data;
}

//.. _get_item_score
function _get_item_score( $id ) {
	list( $items, $score ) = array_values( _ezsqlite([
		'dbname' => 'strid2dbids' ,
		'select' => [ 'dbids', SCORE_COLUMN ] ,
		'where'  => [ 'strid', $id ] ,
	]));
	return [ _filter_item( explode( '|', $items ) ), $score ];
}


//.. _similarity
function _similarity() {
//	return 'similarity'. _getpost('ids') ;
	$ids = explode( '|', _getpost('ids') );
	$items = _filter_item( _obj('dbid')->strid2keys( $ids[0] ) );
	$ret = [];
	foreach ( _obj('dbid')->strid2keys( $ids[1] ) as $i ) {
		if ( in_array( $i, $items ) )
			$ret[] = _obj('dbid')->pop( $i );
	}
	return _long( $ret, 10 );
}

//.. _item2categ
function _item2categ( $item ) {
	return ;
}

//.. _filter_item
function _filter_item( $items ) {
	if ( G_TYPE == 'all' ) return $items;
	$ret = [];
	foreach ( $items as $item ) {
		$categ = [
			'ec' => 'f' ,
			'go' => 'f' ,
			'rt' => 'f' ,
			'pf' => 'h' ,
			'in' => 'h' ,
			'pr' => 'h' ,
			'ct' => 'h' ,
			'sm' => 'h' ,
		][ explode( ':', $item, 2 )[0] ] ?: 'c';
		if ( ! _instr( $categ, G_TYPE ) ) continue;
		$ret[] = $item;
	}
	return $ret;
}

//.. _comparison
function _comparison() {
	list( $items1, $score1 ) = _get_item_score( ID );
	list( $items2, $score2 ) = _get_item_score( ID2 );
	$share = [];
	$table = TR_TOP.TH. 'F&H item'
		.TH. ( new cls_entid( ID ) )->ent_item_img() . ( TEST ? BR. $score1 : '' )
		.TH. ( new cls_entid( ID2 ) )->ent_item_img(). ( TEST ? BR. $score2 : '' )
		. ( TEST ? TH. 'R value' : '' )
	;
	$sum = 0;
	foreach ( $items1 as $i ) {
		if ( ! in_array( $i, $items2 ) ) continue;
		$s = _comparison_item_score( $i );
		$sum += $s;
		$table .= _comparison_row( $i, '@', '@', $s );
	}
	foreach ( $items1 as $i ) {
		if ( in_array( $i, $items2 ) ) continue;
		$table .= _comparison_row( $i, '@', '-' );
	}
	foreach ( $items2 as $i ) {
		if ( in_array( $i, $items1 ) ) continue;
		$table .= _comparison_row( $i, '-', '@' );
	}
	$avg = ( $score1 + $score2 ) / 2;
	return ''
		. _table_2col([
			'sum' => TEST ? $sum : '',
			'avg' => TEST ? $avg : '',
			'Similarity score' => $sum / $avg
		])
//		. BR
		. _t( 'table', $table )
	;
}

function _comparison_row( $fh_id, $c1, $c2, $score = false ) {
	return TR.TH. _obj('dbid')->pop( $fh_id )
		.TD. $c1
		.TD. $c2
		. ( TEST
			? TD. ( $score ?: _comparison_item_score( $fh_id ) )
			: ''
		)
	;
}
function _comparison_item_score( $fh_id ) {
	return _ezsqlite([
		'dbname' => 'dbid2strids' ,
		'select' =>	'score' ,
		'where'  => [ 'dbid', $fh_id ]
	]);
}

//.. _levelbar
function _levelbar( $v ) {
	$v = round( $v * 100 );
	return _div( '.sbar', // . ( $wid != '' ? ' | st:width:200px' : '' ) ,
		_div( ".sbari | st:width:$v%" ) );
}

