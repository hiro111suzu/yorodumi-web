<?php

//. init
define( 'IMG_MODE', 'ym' );
define( 'COLOR_MODE', 'omo' );
//ini_set( 'memory_limit', '1024M' );
require( __DIR__. '/common-web.php' );

define( 'G_RANGE', 50 );
define( 'G_PAGE', _getpost( 'page' ) ?: 0 );
define( 'G_AJAX', _getpost( 'ajax' ) );
define( 'G_ID', strtr( _getpost( 'id' ), [',' => ':'] ) );
define( 'FUNC_ITEMS', [ 'ec', 'go', 'rt' ] );

//_add_lang( 'omofh' );
//_add_fn(   'esearch' );
//_define_term( <<<EOD
//EOD
//);

//$o_id = new cls_entid( G_ID );
//extract( $o_id->get() ); //- $db, $DB, $id, $did
//define( 'ID', $db == 'emdb' ? "e$id" : $id );

//. term
_define_term( <<<EOD
TERM_NO_ITEM
	No item found
	見つかりませんでした
TERM_GMFIT_LINK
	3D structure fitting by gmfit
	gmfitによる立体構造あてはめ

EOD
);

//. ajax reply

if ( G_AJAX == 'list' )
	die( _get_result() );
/*
if ( G_AJAX == 'small' )
	die( _small_search() );
if ( G_AJAX == 'similarity' )
	die( _similarity() );
*/
//. ページ作成
$_simple
->page_conf([
	'title' 	=> _ej( 'Omokage + F&H', 'Omokage + F&H' ),
	'icon'		=> 'YM' ,
	'openabout'	=> false ,
//	'js'		=> [  ] ,
//	'docid' 	=> 'about_fh_search' ,
	'newstag'	=> 'omo' ,
//	'auth_autocomp' => true ,
])

//.. formエリア & resultエリア
/*
->hdiv( 'Query' ,
	_t( 'form | autocomplete:off | #form1', _table_2col([
		'ID'	=> _idinput( G_ID, [ 'acomp' => 'kw' ] ) ,
		'Type'	=> _radiobtns( [ 'name' => 'type', '#item_type', 'on' => G_TYPE ], [
			'all'	=> 'All' ,
			'f'		=> 'Function' ,
			'h'		=> 'Homology' ,
			'c'		=> 'Component' ,
			'hc'	=> 'Homology & Component' ,
		]). ' '. _doc_pop( 'func_homology' ) ,
		'Database' => ID2
			? ''
			: _radiobtns( [ 'name' => 'db', '#filt_db', 'on' => G_DB ], [
				'all'	=> 'All' ,
				'emdb'	=> 'EMDB only' ,
				'pdb'	=> 'PDB only' ,
				'sas'	=> 'SASBDB only' ,
			])
		,
		'Display mode' => ID2
			? '' 
			: _radiobtns( [ 'name' => 'mode', '#disp_mode', 'on' => G_MODE ], [
				'icon'	=> _fa( 'th-large' ). _l( 'images only' ) ,
				'list'	=> _fa( 'list-ul' ). _l( 'as list' ) ,
			])
	])
	. _input( 'hidden', 'name:id2', ID2 )
	. _input( 'submit', 'st: width:20em' )
	)
	. _p( $o_id->ent_item_list() )
	. ( ID2 ? ( new cls_entid( ID2 ) )->ent_item_list() : '' )
//	. _p( '$id: '. ID )
)
*/
//.. result エリア
->hdiv( G_ID ? 'Comparison' : 'Search result',
	G_ID ? _disp_pair() : _get_result() ,
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
/*
->jsvar([ 'postdata' =>[
	'id' => ID ,
	'mode' => G_MODE ,
	'type' => G_TYPE ,
	'db' => G_DB
]])
*/
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
//.. _get_result
function _get_result() {
	$odb = new cls_sqlite( 'omofh' );
	$where = [
		'shape > 0.75' ,
		'hom * 10 < func' ,
		'func > 0.01' ,
//		'( hom < 0.01 OR compos < 0.7 )' ,
		'compos < 0.8' ,
		'size > 10'
	];
	$cnt_all = $odb->cnt();
	$cnt_hit = $odb->cnt( $where );
	
	$result = $odb->qar([
		'select'	=> '*',
		'where'		=> $where ,
		'order by'	=> 'func DESC' ,
		'limit'		=> G_RANGE ,
		'offset'	=> G_PAGE * G_RANGE ,
	]);
	$ret = _p( "$cnt_hit / $cnt_all items" );

	$o_pager = new cls_pager([
		'total'		=> $cnt_hit ,
		'page'		=> G_PAGE ,
		'range'		=> G_RANGE ,
		'pvar'		=> [ 'ajax' => 'list' ] ,
		'div'		=> '#oc_div_result'
	]);

	foreach ( $result as $var ) {
		$id = $shape = $func = $hom = $compos = null;
		extract( $var );
		$ids = $ent = $str_id = null;
		extract( _pair_info( $id ) );
		$ret .= _div( '.clearfix topline', ''
			. _div( '', _imp2([
				_ab( "?id=$id", $id ) ,
				_ab([
					'omoview',
					'id' => strtr( $id, [ ':' => ',' ] ) ,
					'compos' => 1
				], _kv([ 'Shape' => $shape ])) ,
				_ab([
					'fh-search',
					'id'  => $str_id[0] ,
					'id2' => $str_id[1] ,
					'type' => 'f' ,
				], _kv([ 'Function' => $func ]) ) ,
				_ab([
					'fh-search',
					'id'  => $str_id[0] ,
					'id2' => $str_id[1] ,
					'type' => 'hc' ,
				], _kv([ 'Homology' => $hom ]) ) ,
				$compos == 10 ? '' :
				_ab([
					'omoview',
					'id' => strtr( $id, [ ':' => ',' ] ) ,
					'compos' => 1
				], _kv([ 'compos' => $compos ]) ) ,
				
			]) )
			. $ent
		);
	}
	return ''
		. $o_pager->msg()
		. $ret
		. $o_pager->btn()
	;
}

//.. _disp_pair
function _disp_pair() {
	$shape = $func = $hom = $compos = null;
	extract( _ezsqlite([
		'dbname' => 'omofh' ,
		'select' => [ 'shape', 'func', 'hom', 'compos' ] ,
		'where'  => [ 'id', G_ID ] ,
	]) );
	$ids = $ent = $str_id = null;
	extract( _pair_info( G_ID ) );

	//- fh item
	$items = [];
	foreach ( $str_id as $num => $i ) {
		$items[ $num ] =  explode( '|', _ezsqlite([
			'dbname' => 'strid2dbids' ,
			'select' => 'dbids' ,
			'where'  => [ 'strid', $i ] ,
		]));
	}
	$item_set = [];
	foreach ( $items[0] as $i ) {
		if ( ! in_array( $i, $items[1] ) ) continue;
		$item_set[ 
			in_array( explode( ':', $i, 2 )[0], FUNC_ITEMS ) ? 'f' : 'h'
		][] = _obj('dbid')->pop( $i );
	}
	
	//- output
	return ''
		. _simple_table([
			'Shape' =>_ab(
				[
					'omoview',
					'id' => strtr( G_ID, [ ':' => ',' ] ) ,
					'compos' => 1
				],
				_val( $shape )
			). TD. _gmfit( $ids[0], $ids[1] ) ,
			'Function' => _ab(
				[
					'fh-search',
					'id'  => $str_id[0] ,
					'id2' => $str_id[1] ,
					'type' => 'f' ,
				],
				_val( $func )
			). TD. _long( $item_set['f'], 20 ) ,
			'Homology' => _ab(
				[
					'fh-search',
					'id'  => $str_id[0] ,
					'id2' => $str_id[1] ,
					'type' => 'hc' ,
				] ,
				_val( $hom )
			). TD. _long( $item_set['h'],20 ) , 
			'Compos' => _ab(
				[
					'omoview',
					'id' => strtr( G_ID, [ ':' => ',' ] ) ,
					'compos' => 1
				] ,
				$compos < 1 ? _val( $compos, 5 ) : '-' ,
			)
		])
		. $ent
	;
}
//.. _pair_info
function _pair_info( $ids ) {
	$ids = explode( ':', $ids );
	$ent = '';
	$str_id = [];
	foreach ( $ids as $num => $i ) {
		$o_id = new cls_omoid( $i );
		$ent .= _div( '.clearfix', ''
			. $o_id->img_link( $o_id->u_quick )
			. _div( '', $o_id->desc() )
		);
		$str_id[ $num ] = $o_id->db == 'emdb' ? 'e'. $o_id->id : $o_id->id;
	}
	return compact( 'ent', 'str_id', 'ids' );
}

//.. _levelbar
function _levelbar( $v ) {
	return _div( '.sbar', // . ( $wid != '' ? ' | st:width:200px' : '' ) ,
		_div( ".sbari | st:width:$v%" ) );
}

//.. _val
function _val( $v ) {
	return _levelbar( $v * 100 ). round( $v, 4 ); 
}
